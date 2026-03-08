<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Store\Struct;

use Shopwell\Core\Framework\Log\Package;

/**
 * @template-extends StoreCollection<ImageStruct>
 */
#[Package('checkout')]
class ImageCollection extends StoreCollection
{
    protected function getExpectedClass(): string
    {
        return ImageStruct::class;
    }

    protected function getElementFromArray(array $element): StoreStruct
    {
        return ImageStruct::fromArray($element);
    }
}
