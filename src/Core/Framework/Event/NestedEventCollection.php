<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Event;

use Shopwell\Core\Framework\Feature;
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
    /**
     * @deprecated tag:v6.8.0 - Will be removed with the next major as it is unused
     */
    public function getFlatEventList(): self
    {
        Feature::triggerDeprecationOrThrow(
            'v6.8.0.0',
            Feature::deprecatedMethodMessage(self::class, __METHOD__, 'v6.8.0.0'),
        );
        $events = [];

        foreach ($this->getElements() as $event) {
            foreach ($event->getFlatEventList() as $item) {
                $events[] = $item;
            }
        }

        return new self($events);
    }

    public function getApiAlias(): string
    {
        return 'dal_nested_event_collection';
    }

    /**
     * @deprecated tag:v6.8.0 - reason:return-type-change - Will only return string
     *
     * @return TEvent
     *
     * @phpstan-ignore return.phpDocType (Does not work as expected. See https://github.com/phpstan/phpstan/discussions/13728)
     */
    protected function getExpectedClass(): ?string
    {
        return NestedEvent::class;
    }
}
