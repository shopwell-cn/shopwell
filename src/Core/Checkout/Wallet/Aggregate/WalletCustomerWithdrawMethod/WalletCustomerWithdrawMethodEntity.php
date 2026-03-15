<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Wallet\Aggregate\WalletCustomerWithdrawMethod;

use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\Entity;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\Field;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\FieldType;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\PrimaryKey;
use Shopwell\Core\Framework\DataAbstractionLayer\Entity as EntityStruct;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopwell\Core\Framework\Log\Package;

#[Package('checkout')]
#[Entity(WalletCustomerWithdrawMethodEntity::ENTITY_NAME, since: '6.8.0.0', collectionClass: WalletCustomerWithdrawMethodCollection::class)]
class WalletCustomerWithdrawMethodEntity extends EntityStruct
{
    use EntityCustomFieldsTrait;

    final public const string ENTITY_NAME = 'wallet_customer_withdraw_method';

    #[PrimaryKey]
    #[Field(type: FieldType::UUID, api: true)]
    public string $id;
}
