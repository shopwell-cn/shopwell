<?php declare(strict_types=1);

namespace Shopwell\Core\System\DataDict;

use Shopwell\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\Flag\IgnoreInOpenapiSchema;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopwell\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopwell\Core\Framework\Log\Package;

#[Package('data-services')]
class DataDictGroupDefinition extends EntityDefinition
{
    final public const string ENTITY_NAME = 'dict_group';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return DataDictGroupCollection::class;
    }

    public function getEntityClass(): string
    {
        return DataDictGroupEntity::class;
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
            new StringField('code', 'code')->addFlags(new ApiAware(), new Required(), new IgnoreInOpenapiSchema(), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING)),
            new BoolField('active', 'active')->addFlags(new ApiAware())->setDescription('To keep the status of the data dict active, the boolean value is set to `true`.'),
        ]);
    }
}
