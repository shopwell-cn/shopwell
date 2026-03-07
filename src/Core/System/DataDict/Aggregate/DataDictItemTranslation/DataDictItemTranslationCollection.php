<?php declare(strict_types=1);

namespace Shopwell\Core\System\DataDict\Aggregate\DataDictItemTranslation;

use Shopwell\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopwell\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<DataDictItemTranslationEntity>
 */
#[Package('data-services')]
class DataDictItemTranslationCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'dict_group_option_translation_collection';
    }

    protected function getExpectedClass(): string
    {
        return DataDictItemTranslationEntity::class;
    }
}
