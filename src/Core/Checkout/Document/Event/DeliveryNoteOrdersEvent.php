<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Document\Event;

use Shopwell\Core\Framework\Log\Package;

#[Package('after-sales')]
final class DeliveryNoteOrdersEvent extends DocumentOrderEvent
{
}
