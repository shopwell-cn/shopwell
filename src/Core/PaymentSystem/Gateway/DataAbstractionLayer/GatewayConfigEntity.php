<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Gateway\DataAbstractionLayer;

use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\CustomFields;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\Entity;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\Field;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\FieldType;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\PrimaryKey;
use Shopwell\Core\Framework\DataAbstractionLayer\Entity as EntityStruct;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopwell\Core\Framework\Log\Package;

#[Package('payment-system')]
#[Entity(GatewayConfigEntity::ENTITY_NAME, collectionClass: GatewayConfigCollection::class)]
class GatewayConfigEntity extends EntityStruct
{
    use EntityCustomFieldsTrait;

    final public const string ENTITY_NAME = 'payment_gateway_config';

    #[PrimaryKey]
    #[Field(type: FieldType::UUID, api: true)]
    public string $id;

    /**
     * @var array<string,mixed>
     */
    #[Field(type: FieldType::JSON, api: true)]
    public array $config;

    #[Field(type: FieldType::STRING, api: true)]
    public string $name;

    #[Field(type: FieldType::STRING, api: true)]
    public string $factory;

    #[Field(type: FieldType::BOOL, api: true)]
    public bool $active;

    /**
     * @var array<mixed>|null
     */
    #[CustomFields(true)]
    protected ?array $customFields = null;
}
