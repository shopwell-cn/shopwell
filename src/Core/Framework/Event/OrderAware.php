<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Event;

use Shopwell\Core\Framework\Log\Package;

#[Package('framework')]
#[IsFlowEventAware]
interface OrderAware
{
    public const string ORDER = 'order';

    public const string ORDER_ID = 'orderId';

    public function getOrderId(): string;
}
