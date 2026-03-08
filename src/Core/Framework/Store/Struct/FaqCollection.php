<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Store\Struct;

use Shopwell\Core\Framework\Log\Package;

/**
 * @template-extends StoreCollection<FaqStruct>
 */
#[Package('checkout')]
class FaqCollection extends StoreCollection
{
    protected function getExpectedClass(): string
    {
        return FaqStruct::class;
    }

    protected function getElementFromArray(array $element): StoreStruct
    {
        return FaqStruct::fromArray($element);
    }
}
