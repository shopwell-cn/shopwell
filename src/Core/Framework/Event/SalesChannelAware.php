<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Event;

use Shopwell\Core\Framework\Log\Package;

#[Package('fundamentals@after-sales')]
#[IsFlowEventAware]
interface SalesChannelAware
{
    public function getSalesChannelId(): string;
}
