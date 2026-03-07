<?php declare(strict_types=1);

namespace Shopwell\Core\System\DataDict\Aggregate\DataDictGroupTranslation;

use Shopwell\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopwell\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<DataDictGroupTranslationEntity>
 */
#[Package('data-services')]
class DataDictGroupTranslationCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'dict_group_translation_collection';
    }

    protected function getExpectedClass(): string
    {
        return DataDictGroupTranslationEntity::class;
    }
}
