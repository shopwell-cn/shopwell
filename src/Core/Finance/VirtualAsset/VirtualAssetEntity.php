<?php declare(strict_types=1);

namespace Shopwell\Core\Finance\VirtualAsset;

use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\Entity;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\Field;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\FieldType;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\PrimaryKey;
use Shopwell\Core\Framework\DataAbstractionLayer\Entity as EntityStruct;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopwell\Core\Framework\Log\Package;

#[Package('fundamentals@framework')]
#[Entity(VirtualAssetEntity::ENTITY_NAME, since: '6.8.0.0')]
class VirtualAssetEntity extends EntityStruct
{
    use EntityCustomFieldsTrait;

    final public const string ENTITY_NAME = 'virtual_asset';

    #[PrimaryKey]
    #[Field(type: FieldType::UUID, api: true)]
    public string $id;

    #[Field(type: FieldType::INT, api: true)]
    public float $version;

    #[Field(type: FieldType::STRING, api: true)]
    public string $identifier;

    #[Field(type: FieldType::STRING, api: true)]
    public string $referencedId;
}
