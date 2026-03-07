<?php declare(strict_types=1);

namespace Shopwell\Core\System\DataDict\Aggregate\DataDictItem;

use Shopwell\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopwell\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<DataDictItemEntity>
 */
#[Package('data-services')]
class DataDictItemCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'dict_group_option_collection';
    }

    protected function getExpectedClass(): string
    {
        return DataDictItemEntity::class;
    }
}
