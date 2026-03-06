<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Document\Zugferd;

use Shopwell\Core\Checkout\Document\DocumentConfiguration;
use Shopwell\Core\Checkout\Order\OrderEntity;
use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\Log\Package;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('after-sales')]
class ZugferdInvoiceGeneratedEvent extends Event
{
    public function __construct(
        public readonly ZugferdDocument $document,
        public readonly OrderEntity $order,
        public readonly DocumentConfiguration $config,
        public readonly Context $context
    ) {
    }
}
