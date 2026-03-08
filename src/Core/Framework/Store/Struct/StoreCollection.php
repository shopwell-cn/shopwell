<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Store\Struct;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Struct\Collection;

/**
 * @template TElement of StoreStruct
 *
 * @template-extends Collection<TElement>
 */
#[Package('checkout')]
abstract class StoreCollection extends Collection
{
    /**
     * @param array<TElement|array<string, mixed>> $elements
     */
    public function __construct(iterable $elements = [])
    {
        foreach ($elements as $element) {
            if (\is_array($element)) {
                $element = $this->getElementFromArray($element);
            }

            $this->add($element);
        }
    }

    protected function getExpectedClass(): string
    {
        return StoreStruct::class;
    }

    /**
     * @param array<string, mixed> $element
     *
     * @return TElement
     */
    abstract protected function getElementFromArray(array $element): StoreStruct;
}
