<?php declare(strict_types=1);

namespace Shopwell\Core\System\DataDict\Aggregate\DataDictItem;

use Shopwell\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\ChildCountField;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\ChildrenAssociationField;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\ParentAssociationField;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\ParentFkField;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\TreePathField;
use Shopwell\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopwell\Core\Framework\Log\Package;

#[Package('data-services')]
class DataDictItemDefinition extends EntityDefinition
{
    final public const string ENTITY_NAME = 'dict_group';

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
        ]);
    }
}
