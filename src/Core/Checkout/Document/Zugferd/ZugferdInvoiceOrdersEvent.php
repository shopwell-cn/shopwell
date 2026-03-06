<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Document\Zugferd;

use Shopwell\Core\Checkout\Document\Event\DocumentOrderEvent;
use Shopwell\Core\Framework\Log\Package;

#[Package('after-sales')]
final class ZugferdInvoiceOrdersEvent extends DocumentOrderEvent
{
}
