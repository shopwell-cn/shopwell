<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Store\Struct;

use Shopwell\Core\Framework\Log\Package;

/**
 * @template-extends StoreCollection<StoreCategoryStruct>
 */
#[Package('checkout')]
class StoreCategoryCollection extends StoreCollection
{
    protected function getExpectedClass(): string
    {
        return StoreCategoryStruct::class;
    }

    protected function getElementFromArray(array $element): StoreStruct
    {
        return StoreCategoryStruct::fromArray($element);
    }
}
