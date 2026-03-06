<?php declare(strict_types=1);

namespace Shopwell\Core\Content\Product;

use Shopwell\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity;
use Shopwell\Core\Content\Property\PropertyGroupCollection;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopwell\Core\Framework\DataAbstractionLayer\PartialEntity;
use Shopwell\Core\Framework\Log\Package;

#[Package('inventory')]
abstract class AbstractPropertyGroupSorter
{
    abstract public function getDecorated(): AbstractPropertyGroupSorter;

    /**
     * @param EntityCollection<PropertyGroupOptionEntity|PartialEntity> $options
     */
    abstract public function sort(EntityCollection $options): PropertyGroupCollection;
}
