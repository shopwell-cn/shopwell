<?php declare(strict_types=1);

namespace Shopwell\Core\System\DataDict;

use Shopwell\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopwell\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<DataDictGroupEntity>
 */
#[Package('data-services')]
class DataDictGroupCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'dict_group_collection';
    }

    protected function getExpectedClass(): string
    {
        return DataDictGroupEntity::class;
    }
}
