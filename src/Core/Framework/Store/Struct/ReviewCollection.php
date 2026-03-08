<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Store\Struct;

use Shopwell\Core\Framework\Log\Package;

/**
 * @template-extends StoreCollection<ReviewStruct>
 */
#[Package('checkout')]
class ReviewCollection extends StoreCollection
{
    protected function getExpectedClass(): string
    {
        return ReviewStruct::class;
    }

    protected function getElementFromArray(array $element): StoreStruct
    {
        return ReviewStruct::fromArray($element);
    }
}
