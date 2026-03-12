<?php declare(strict_types=1);

namespace Shopwell\Core\Payment\Api\Handler;

use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Uuid\Uuid;
use Shopwell\Core\Framework\Validation\DataBag\DataBag;
use Shopwell\Core\Payment\Api\Event\PaymentOrderConvertedEvent;
use Shopwell\Core\Payment\Order\PaymentOrderEntity;
use Shopwell\Core\System\NumberRange\ValueGenerator\NumberRangeValueGeneratorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

#[Package('payment-system')]
class PaymentOrderConverter
{
    public function __construct(
        protected EventDispatcherInterface $eventDispatcher,
        private readonly NumberRangeValueGeneratorInterface $numberRangeValueGenerator,
    ) {
    }

    /**
     * @return array<string, mixed|float|string|array<int, array<string, string|int|bool|mixed>>|null>
     */
    public function convertToOrder(DataBag $request, Context $context): array
    {
        $data = [
            'outOrderNo' => $request->getString('outOrderNo'),
            'amount' => $request->getDigits('amount'),
            'currency' => $request->getString('currency'),
            'subject' => $request->getString('subject'),
            'body' => $request->getString('body'),
        ];

        $data['paymentOrderNumber'] = $this->numberRangeValueGenerator->getValue(
            PaymentOrderEntity::ENTITY_NAME,
            $context,
            null
        );

        $data['id'] = Uuid::randomHex();

        $event = new PaymentOrderConvertedEvent($request, $data, $context);
        $this->eventDispatcher->dispatch($event);

        return $event->getConverted();
    }
}
