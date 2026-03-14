<?php declare(strict_types=1);

namespace Shopwell\Core\System\DataDict\Aggregate\DataDictItem;

use Shopwell\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\ChildCountField;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\ChildrenAssociationField;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\ParentAssociationField;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\ParentFkField;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\TreePathField;
use Shopwell\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\System\DataDict\Aggregate\DataDictItemTranslation\DataDictItemTranslationDefinition;
use Shopwell\Core\System\DataDict\DataDictGroupDefinition;

#[Package('data-services')]
class DataDictItemDefinition extends EntityDefinition
{
    final public const string ENTITY_NAME = 'data_dict_item';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return DataDictItemCollection::class;
    }

    public function getEntityClass(): string
    {
        return DataDictItemEntity::class;
    }

    public function getDefaults(): array
    {
        return ['position' => 1];
    }

    public function since(): ?string
    {
        return '6.0.0.0';
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            new IdField('id', 'id')->addFlags(new Required(), new PrimaryKey()),
            new StringField('option_value', 'optionValue')->addFlags(new Required(), new ApiAware()),
            new ParentFkField(self::class)->addFlags(new ApiAware()),
            new ChildCountField()->addFlags(new ApiAware()),
            new TreePathField('path', 'path')->addFlags(new ApiAware()),
            new ParentAssociationField(self::class, 'id')->addFlags(new ApiAware()),
            new ChildrenAssociationField(self::class)->addFlags(new ApiAware()),
            new BoolField('active', 'active'),
            new IntField('position', 'position'),
            new FkField('group_id', 'groupId', DataDictGroupDefinition::class)->addFlags(new ApiAware(), new Required()),
            new ManyToOneAssociationField('group', 'group_id', DataDictGroupDefinition::class, 'id', false),
            new TranslatedField('customFields')->addFlags(new ApiAware()),
            new TranslatedField('description')->addFlags(new ApiAware()),
            new TranslatedField('name')->addFlags(new ApiAware(), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING)),
            new TranslationsAssociationField(DataDictItemTranslationDefinition::class, 'data_dict_item_id')->addFlags(new Required(), new CascadeDelete()),
        ]);
    }
}
