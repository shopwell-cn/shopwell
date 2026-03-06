<?php declare(strict_types=1);

namespace Shopwell\Core\Content\Product\SalesChannel;

use Shopwell\Core\Content\Product\ProductCollection;
use Shopwell\Core\Framework\Log\Package;

#[Package('inventory')]
class SalesChannelProductCollection extends ProductCollection
{
    protected function getExpectedClass(): string
    {
        return SalesChannelProductEntity::class;
    }
}
