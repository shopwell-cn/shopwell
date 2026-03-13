<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Payment;

use Psr\Log\LoggerInterface;
use Shopwell\Core\Checkout\Cart\Cart;
use Shopwell\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionCollection;
use Shopwell\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopwell\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStateHandler;
use Shopwell\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStates;
use Shopwell\Core\Checkout\Payment\Cart\AbstractPaymentTransactionStructFactory;
use Shopwell\Core\Checkout\Payment\Cart\PaymentHandler\PaymentHandlerRegistry;
use Shopwell\Core\Checkout\Payment\Cart\Token\PaymentToken;
use Shopwell\Core\Checkout\Payment\Cart\Token\PaymentTokenGenerator;
use Shopwell\Core\Checkout\Payment\Cart\Token\PaymentTokenLifecycle;
use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopwell\Core\Framework\HttpException;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Struct\ArrayStruct;
use Shopwell\Core\Framework\Struct\Struct;
use Shopwell\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopwell\Core\System\SalesChannel\SalesChannelContext;
use Shopwell\Core\System\StateMachine\Loader\InitialStateIdLoader;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * @final
 */
#[Package('checkout')]
class PaymentProcessor
{
    /**
     * @param EntityRepository<OrderTransactionCollection> $orderTransactionRepository
     *
     * @internal
     */
    public function __construct(
        private readonly PaymentTokenGenerator $paymentTokenGenerator,
        private readonly PaymentTokenLifecycle $paymentTokenLifecycle,
        private readonly PaymentHandlerRegistry $paymentHandlerRegistry,
        private readonly EntityRepository $orderTransactionRepository,
        private readonly OrderTransactionStateHandler $transactionStateHandler,
        private readonly LoggerInterface $logger,
        private readonly AbstractPaymentTransactionStructFactory $paymentTransactionStructFactory,
        private readonly InitialStateIdLoader $initialStateIdLoader,
        private readonly RouterInterface $router,
    ) {
    }

    public function pay(
        string $orderId,
        Request $request,
        SalesChannelContext $salesChannelContext,
        ?string $finishUrl = null,
        ?string $errorUrl = null,
    ): ?RedirectResponse {
        $transaction = $this->getCurrentOrderTransaction($orderId, $salesChannelContext->getContext());
        if (!$transaction) {
            return null;
        }

        $token = $this->getToken($transaction, $finishUrl, $errorUrl, $salesChannelContext);
        $encodedToken = $this->encodeToken($token);

        $returnUrl = $this->getReturnUrl($encodedToken);

        try {
            $paymentHandler = $this->paymentHandlerRegistry->getPaymentMethodHandler($transaction->getPaymentMethodId());
            if (!$paymentHandler) {
                throw PaymentException::unknownPaymentMethodById($transaction->getPaymentMethodId());
            }

            $transactionStruct = $this->paymentTransactionStructFactory->build($transaction->getId(), $salesChannelContext->getContext(), $returnUrl);
            $validationStruct = $transaction->getValidationData() ? new ArrayStruct($transaction->getValidationData()) : null;

            $response = $paymentHandler->pay($request, $transactionStruct, $salesChannelContext->getContext(), $validationStruct);
            if ($response instanceof RedirectResponse) {
                $encodedToken = null;
            }

            return $response;
        } catch (\Throwable $e) {
            $this->logger->error('An error occurred during processing the payment', ['orderTransactionId' => $transaction->getId(), 'exceptionMessage' => $e->getMessage(), 'exceptionTrace' => $e->getTraceAsString(), 'exception' => $e]);
            $this->transactionStateHandler->fail($transaction->getId(), $salesChannelContext->getContext());
            if ($errorUrl !== null) {
                $errorCode = $e instanceof HttpException ? $e->getErrorCode() : PaymentException::PAYMENT_PROCESS_ERROR;

                return new RedirectResponse(\sprintf('%s%serror-code=%s', $errorUrl, parse_url($errorUrl, \PHP_URL_QUERY) ? '&' : '?', $errorCode));
            }

            throw $e;
        } finally {
            // has been nulled, if response is RedirectResponse, therefore we have a finalize step
            if ($encodedToken) {
                if ($token->jti !== null) {
                    $this->paymentTokenLifecycle->invalidateToken($token->jti);
                }
            }
        }
    }

    public function finalize(PaymentToken $token, Request $request, SalesChannelContext $context): void
    {
        $transactionId = $token->transactionId;
        $paymentMethodId = $token->paymentMethodId;

        $paymentHandler = $this->paymentHandlerRegistry->getPaymentMethodHandler($paymentMethodId);
        if (!$paymentHandler) {
            throw PaymentException::unknownPaymentMethodById($paymentMethodId);
        }

        try {
            $transactionStruct = $this->paymentTransactionStructFactory->build($transactionId, $context->getContext());
            $paymentHandler->finalize($request, $transactionStruct, $context->getContext());
        } catch (\Throwable $e) {
            if ($e instanceof PaymentException && $e->getErrorCode() === PaymentException::PAYMENT_CUSTOMER_CANCELED_EXTERNAL) {
                $this->transactionStateHandler->cancel($transactionId, $context->getContext());
            } else {
                $this->logger->error('An error occurred during finalizing async payment', ['orderTransactionId' => $transactionId, 'exceptionMessage' => $e->getMessage(), 'exception' => $e]);
                $this->transactionStateHandler->fail($transactionId, $context->getContext());
            }
            throw $e;
        } finally {
            if ($token->jti !== null) {
                $this->paymentTokenLifecycle->invalidateToken($token->jti);
            }
        }
    }

    public function validate(
        Cart $cart,
        RequestDataBag $dataBag,
        SalesChannelContext $salesChannelContext
    ): ?Struct {
        try {
            $paymentHandler = $this->paymentHandlerRegistry->getPaymentMethodHandler($salesChannelContext->getPaymentMethod()->getId());
            if (!$paymentHandler) {
                throw PaymentException::unknownPaymentMethodById($salesChannelContext->getPaymentMethod()->getId());
            }

            $struct = $paymentHandler->validate($cart, $dataBag, $salesChannelContext);
            $cart->getTransactions()->first()?->setValidationStruct($struct);

            return $struct;
        } catch (\Throwable $e) {
            $this->logger->error(
                'An error occurred during processing the validation of the payment. The order has not been placed yet.',
                ['customerId' => $salesChannelContext->getCustomerId(), 'exceptionMessage' => $e->getMessage(), 'exception' => $e]
            );

            throw $e;
        }
    }

    private function getCurrentOrderTransaction(string $orderId, Context $context): ?OrderTransactionEntity
    {
        $criteria = new Criteria()
            ->addFilter(new EqualsFilter('stateId', $this->initialStateIdLoader->get(OrderTransactionStates::STATE_MACHINE)))
            ->addFilter(new EqualsFilter('orderId', $orderId))
            ->addSorting(new FieldSorting('createdAt', FieldSorting::DESCENDING))
            ->setLimit(1);

        $transaction = $this->orderTransactionRepository->search($criteria, $context)->getEntities()->first();

        if (!$transaction) {
            // check, if there are no transactions at all or just not with non-initial state
            $criteria->resetFilters();
            $criteria->addFilter(new EqualsFilter('orderId', $orderId));

            if ($this->orderTransactionRepository->searchIds($criteria, $context)->firstId()) {
                return null;
            }

            throw PaymentException::invalidOrder($orderId);
        }

        return $transaction;
    }

    private function getToken(OrderTransactionEntity $transaction, ?string $finishUrl, ?string $errorUrl, SalesChannelContext $salesChannelContext): PaymentToken
    {
        $token = new PaymentToken();
        $token->paymentMethodId = $transaction->getPaymentMethodId();
        $token->salesChannelId = $salesChannelContext->getSalesChannelId();
        $token->transactionId = $transaction->getId();
        $token->finishUrl = $finishUrl;
        $token->errorUrl = $errorUrl;

        return $token;
    }

    private function encodeToken(PaymentToken $token): string
    {
        $encoded = $this->paymentTokenGenerator->encode($token);

        if (!$token->jti || !$token->exp) {
            throw PaymentException::invalidToken($encoded);
        }

        $this->paymentTokenLifecycle->addToken($token->jti, $token->exp);

        return $encoded;
    }

    private function getReturnUrl(string $token): string
    {
        $parameter = ['_sw_payment_token' => $token];

        return $this->router->generate('payment.finalize.transaction', $parameter, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
