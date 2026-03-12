<?php declare(strict_types=1);

namespace Shopwell\Core\Payment\Api\Event;

use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\Event\NestedEvent;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Validation\DataBag\DataBag;

#[Package('payment-system')]
class PaymentOrderConvertedEvent extends NestedEvent
{
    /**
     * @var array<mixed>
     */
    private array $converted;

    /**
     * @param array<mixed> $originalConverted
     */
    public function __construct(
        private readonly DataBag $payOrderRequest,
        private readonly array $originalConverted,
        private readonly Context $context,
    ) {
        $this->converted = $this->originalConverted;
    }

    /**
     * @return mixed[]
     */
    public function getConverted(): array
    {
        return $this->converted;
    }

    public function getRequest(): DataBag
    {
        return $this->payOrderRequest;
    }

    /**
     * @return mixed[]
     */
    public function getOriginalConverted(): array
    {
        return $this->originalConverted;
    }

    public function getContext(): Context
    {
        return $this->context;
    }
}
