<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Event;

use Shopwell\Core\Framework\Log\Package;

#[Package('fundamentals@after-sales')]
#[IsFlowEventAware]
interface OrderAware
{
    public const ORDER = 'order';

    public const ORDER_ID = 'orderId';

    public function getOrderId(): string;
}
