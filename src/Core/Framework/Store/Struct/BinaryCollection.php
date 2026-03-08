<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Store\Struct;

use Shopwell\Core\Framework\Log\Package;

/**
 * @template-extends StoreCollection<BinaryStruct>
 */
#[Package('checkout')]
class BinaryCollection extends StoreCollection
{
    protected function getExpectedClass(): string
    {
        return BinaryStruct::class;
    }

    protected function getElementFromArray(array $element): StoreStruct
    {
        return BinaryStruct::fromArray($element);
    }
}
