<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Api\Handler;

use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopwell\Core\Framework\Extensions\ExtensionDispatcher;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Validation\DataBag\DataBag;
use Shopwell\Core\Framework\Validation\DataValidationDefinition;
use Shopwell\Core\Framework\Validation\DataValidator;
use Shopwell\Core\PaymentSystem\Api\Extension\PaymentSystemPlaceOrderExtension;
use Shopwell\Core\PaymentSystem\Api\PaymentSystemApiException;
use Shopwell\Core\PaymentSystem\Api\Response\PaymentOrderResponse;
use Shopwell\Core\PaymentSystem\Api\Struct\PaymentOrderPlaceResult;
use Shopwell\Core\PaymentSystem\Order\PaymentOrderCollection;
use Shopwell\Core\PaymentSystem\Order\PaymentOrderEntity;
use Shopwell\Core\Profiling\Profiler;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Type;

#[Package('payment-system')]
readonly class PaymentOrderHandler
{
    /**
     * @internal
     *
     * @param EntityRepository<PaymentOrderCollection> $paymentOrderRepository
     */
    public function __construct(
        private DataValidator $validator,
        private PaymentOrderConverter $converter,
        private EntityRepository $paymentOrderRepository,
        private ExtensionDispatcher $extensions,
    ) {
    }

    public function createOrder(DataBag $request, Context $context): PaymentOrderResponse
    {
        $placed = $this->extensions->publish(
            name: PaymentSystemPlaceOrderExtension::NAME,
            extension: new PaymentSystemPlaceOrderExtension($request, $context),
            function: $this->place(...)
        );

        $orderId = $placed->orderId;

        $criteria = new Criteria([$orderId]);

        $orderEntity = Profiler::trace('payment-system::order-loading', function () use ($criteria, $context): ?PaymentOrderEntity {
            return $this->paymentOrderRepository->search($criteria, $context)->getEntities()->first();
        });

        if (!$orderEntity) {
            throw PaymentSystemApiException::invalidPaymentOrderNotStored($orderId);
        }

        return PaymentOrderResponse::success($orderEntity);
    }

    private function place(DataBag $request, Context $context): PaymentOrderPlaceResult
    {
        $this->validateOrderData($request->all());

        $order = $this->converter->convertToOrder($request, $context);

        $context->scope(Context::SYSTEM_SCOPE, function (Context $context) use ($order): void {
            $this->paymentOrderRepository->create([$order], $context);
        });

        return new PaymentOrderPlaceResult($order['id']);
    }

    /**
     * @param array<string,mixed> $data
     */
    private function validateOrderData(array $data): void
    {
        $definition = $this->getOrderCreateValidationDefinition();
        $violations = $this->validator->getViolations($data, $definition);
        if (!$violations->count()) {
            return;
        }
    }

    private function getOrderCreateValidationDefinition(): DataValidationDefinition
    {
        return new DataValidationDefinition()
            ->add('outOrderNo', new NotBlank(), new Type('string'), new Length(min: 6, max: 32))
            ->add('paymentType', new NotBlank(), new Type('string'))
            ->add('subject', new NotBlank(), new Type('string'))
            ->add('body', new NotBlank(), new Type('string'))
            ->add(
                'amount',
                new NotBlank(),
                new Type('numeric'),
                new GreaterThan(0.01),
                new Regex(pattern: '/^\d+(\.\d{1,2})?$/')
            );
    }
}
