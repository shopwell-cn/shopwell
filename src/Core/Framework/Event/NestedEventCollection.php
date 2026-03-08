<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Event;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Struct\Collection;

/**
 * @template TEvent of NestedEvent = NestedEvent
 *
 * @extends Collection<TEvent>
 */
#[Package('framework')]
class NestedEventCollection extends Collection
{
    public function getApiAlias(): string
    {
        return 'dal_nested_event_collection';
    }

    /**
     * @return TEvent
     *
     * @phpstan-ignore return.phpDocType (Does not work as expected. See https://github.com/phpstan/phpstan/discussions/13728)
     */
    protected function getExpectedClass(): string
    {
        return NestedEvent::class;
    }
}
