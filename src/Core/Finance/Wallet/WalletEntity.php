<?php declare(strict_types=1);

namespace Shopwell\Core\Finance\Wallet;

use Shopwell\Core\Finance\Wallet\Aggregate\WalletTransaction\WalletTransactionEntity;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\CustomFields;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\Entity;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\Field;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\FieldType;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\OnDelete;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\OneToMany;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\PrimaryKey;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\Protection;
use Shopwell\Core\Framework\DataAbstractionLayer\Entity as EntityStruct;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopwell\Core\Framework\Log\Package;

#[Package('fundamentals@framework')]
#[Entity(WalletEntity::ENTITY_NAME, since: '6.8.0.0')]
class WalletEntity extends EntityStruct
{
    use EntityCustomFieldsTrait;

    final public const string ENTITY_NAME = 'wallet';

    #[PrimaryKey]
    #[Field(type: FieldType::UUID, api: true)]
    public string $id;

    #[Field(type: FieldType::INT, api: true)]
    public float $version;

    #[Field(type: FieldType::STRING, api: true)]
    public string $identifier;

    #[Field(type: FieldType::STRING, api: true)]
    public string $referencedId;

    #[Field(type: FieldType::BOOL, api: true)]
    public float $active;

    #[Protection([Protection::SYSTEM_SCOPE])]
    #[Field(type: FieldType::FLOAT, api: true)]
    public float $balance;

    #[Protection([Protection::SYSTEM_SCOPE])]
    #[Field(type: FieldType::FLOAT, api: true)]
    public float $frozenBalance;

    #[Protection([Protection::SYSTEM_SCOPE])]
    #[Field(type: FieldType::FLOAT, api: true)]
    public float $availableBalance;

    /**
     * @var array<string, WalletTransactionEntity>|null
     */
    #[OneToMany(entity: 'wallet_transaction', ref: 'wallet_id', onDelete: OnDelete::CASCADE, api: true)]
    public ?array $transactions = null;

    /**
     * @var array<mixed>|null
     */
    #[CustomFields(true)]
    protected ?array $customFields = null;
}
