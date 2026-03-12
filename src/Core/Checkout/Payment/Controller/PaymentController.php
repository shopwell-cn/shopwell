<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Payment\Controller;

use Shopwell\Core\Checkout\Cart\Order\OrderConverter;
use Shopwell\Core\Checkout\Order\OrderCollection;
use Shopwell\Core\Checkout\Payment\Cart\Token\PaymentToken;
use Shopwell\Core\Checkout\Payment\Cart\Token\PaymentTokenGenerator;
use Shopwell\Core\Checkout\Payment\Cart\Token\PaymentTokenLifecycle;
use Shopwell\Core\Checkout\Payment\Cart\Token\TokenStruct;
use Shopwell\Core\Checkout\Payment\PaymentException;
use Shopwell\Core\Checkout\Payment\PaymentProcessor;
use Shopwell\Core\Framework\Adapter\Request\RequestParamHelper;
use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopwell\Core\Framework\Feature;
use Shopwell\Core\Framework\JWT\JWTException;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\ShopwellException;
use Shopwell\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Package('checkout')]
class PaymentController extends AbstractController
{
    /**
     * @internal
     *
     * @param EntityRepository<OrderCollection> $orderRepository
     */
    public function __construct(
        private readonly PaymentProcessor $paymentProcessor,
        private readonly OrderConverter $orderConverter,
        private readonly PaymentTokenGenerator $paymentTokenGenerator,
        private readonly PaymentTokenLifecycle $paymentTokenLifecycle,
        private readonly EntityRepository $orderRepository
    ) {
    }

    /**
     * The route scope could not be defined as this route is called from external.
     * An API route scope would normally imply an authentication, which external callers could not provide.
     * Only a storefront route scope could also not be used, as it also needs to work on headless environments.
     *
     * @phpstan-ignore shopwell.routeScope
     */
    #[Route(
        path: '/payment/finalize-transaction',
        name: 'payment.finalize.transaction',
        methods: [Request::METHOD_GET, Request::METHOD_POST]
    )]
    public function finalizeTransaction(Request $request): Response
    {
        $paymentToken = RequestParamHelper::get($request, '_sw_payment_token');

        if (!\is_string($paymentToken)) {
            throw PaymentException::missingRequestParameter('_sw_payment_token');
        }

        try {
            $token = $this->paymentTokenGenerator->decode($paymentToken);
        } catch (JWTException $e) {
            try {
                // try to decode without validation for graceful error handling
                $token = $this->paymentTokenGenerator->decode($paymentToken, true);
                if ($token->jti !== null) {
                    $this->paymentTokenLifecycle->invalidateToken($token->jti);
                }
            } catch (\Throwable $e) {
                throw PaymentException::invalidToken($paymentToken, $e);
            }

            return $this->handleError($e, $token);
        } catch (\Throwable $e) {
            throw PaymentException::invalidToken($paymentToken, $e);
        }

        if (Feature::isActive('REPEATED_PAYMENT_FINALIZE') && $token->jti !== null && !$this->paymentTokenLifecycle->isConsumable($token->jti)) {
            return $this->handleFinish($token);
        }

        $salesChannelContext = $this->assembleSalesChannelContext($token->transactionId, $token->jti ?? $paymentToken);

        try {
            $deprecatedParameter = null;
            Feature::silent('v6.8.0.0', static function () use (&$deprecatedParameter): void {
                $deprecatedParameter ??= new TokenStruct();
            });
            \assert($deprecatedParameter instanceof TokenStruct);

            $this->paymentProcessor->finalize(
                $deprecatedParameter,
                $request,
                $salesChannelContext,
                $token,
            );

            return $this->handleFinish($token);
        } catch (\Throwable $e) {
            return $this->handleError($e, $token);
        } finally {
            if ($token->jti !== null) {
                $this->paymentTokenLifecycle->invalidateToken($token->jti);
            }
        }
    }

    private function handleError(\Throwable $exception, ?PaymentToken $token): Response
    {
        $errorUrl = $token?->errorUrl;
        if ($errorUrl === null) {
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        if ($exception instanceof ShopwellException) {
            $errorUrl .= (parse_url($errorUrl, \PHP_URL_QUERY) ? '&' : '?') . 'error-code=' . $exception->getErrorCode();
        }

        return new RedirectResponse($errorUrl);
    }

    private function handleFinish(PaymentToken $token): Response
    {
        if ($token->finishUrl) {
            return new RedirectResponse($token->finishUrl);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function assembleSalesChannelContext(string $transactionId, string $tokenString): SalesChannelContext
    {
        $context = Context::createDefaultContext();

        $criteria = new Criteria()
            ->addFilter(new EqualsFilter('transactions.id', $transactionId))
            ->addAssociations(['transactions.stateMachineState', 'orderCustomer']);

        $order = $this->orderRepository->search($criteria, $context)->getEntities()->first();
        if (!$order) {
            throw PaymentException::invalidToken($tokenString);
        }

        return $this->orderConverter->assembleSalesChannelContext($order, $context);
    }
}
