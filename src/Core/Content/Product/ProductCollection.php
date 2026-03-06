<?php declare(strict_types=1);

namespace Shopwell\Core\Content\Product;

use Shopwell\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopwell\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<ProductEntity>
 */
#[Package('inventory')]
class ProductCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'product_collection';
    }

    protected function getExpectedClass(): string
    {
        return ProductEntity::class;
    }
}
