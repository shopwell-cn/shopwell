<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Wallet\Aggregate\WalletWithdraw;

use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\CustomFields;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\Entity;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\Field;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\FieldType;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\ForeignKey;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\PrimaryKey;
use Shopwell\Core\Framework\DataAbstractionLayer\Entity as EntityStruct;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopwell\Core\Framework\Log\Package;

#[Package('checkout')]
#[Entity(WalletWithdrawEntity::ENTITY_NAME, since: '6.8.0.0', collectionClass: WalletWithdrawCollection::class)]
class WalletWithdrawEntity extends EntityStruct
{
    use EntityCustomFieldsTrait;

    final public const string ENTITY_NAME = 'wallet_withdraw';

    #[PrimaryKey]
    #[Field(type: FieldType::UUID, api: true)]
    public string $id;

    #[ForeignKey(entity: 'customer', api: true)]
    public string $walletId;

    /**
     * @var array<mixed>|null
     */
    #[CustomFields(true)]
    protected ?array $customFields = null;
}
