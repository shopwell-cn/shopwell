<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Event;

use Shopwell\Core\Framework\Log\Package;

#[Package('after-sales')]
#[\Attribute(\Attribute::TARGET_CLASS)]
final class IsFlowEventAware
{
}
