<?php declare(strict_types=1);

namespace Shopwell\Core\System\DataDict\Aggregate\DataDictItemTranslation;

use Shopwell\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopwell\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\System\DataDict\Aggregate\DataDictItem\DataDictItemDefinition;

#[Package('data-services')]
class DataDictItemTranslationDefinition extends EntityTranslationDefinition
{
    final public const string ENTITY_NAME = 'dict_group_option_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return DataDictItemTranslationCollection::class;
    }

    public function getEntityClass(): string
    {
        return DataDictItemTranslationEntity::class;
    }

    public function since(): ?string
    {
        return '6.0.0.0';
    }

    protected function getParentDefinitionClass(): string
    {
        return DataDictItemDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            new StringField('name', 'name')->addFlags(new ApiAware(), new Required()),
            new LongTextField('description', 'description')->addFlags(new ApiAware()),
            new CustomFields()->addFlags(new ApiAware()),
        ]);
    }
}
