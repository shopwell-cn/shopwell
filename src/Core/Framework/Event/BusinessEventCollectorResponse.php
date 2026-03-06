<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Event;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Struct\Collection;

/**
 * @extends Collection<BusinessEventDefinition>
 */
#[Package('fundamentals@after-sales')]
class BusinessEventCollectorResponse extends Collection
{
    protected function getExpectedClass(): ?string
    {
        return BusinessEventDefinition::class;
    }
}
