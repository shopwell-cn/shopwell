<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Order\Aggregate\PaymentOrderTransaction;

use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\Entity;
use Shopwell\Core\Framework\DataAbstractionLayer\Entity as EntityStruct;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopwell\Core\Framework\Log\Package;

#[Package('payment-system')]
#[Entity(PaymentOrderTransactionEntity::ENTITY_NAME, collectionClass: PaymentOrderTransactionCollection::class)]
class PaymentOrderTransactionEntity extends EntityStruct
{
    use EntityCustomFieldsTrait;
    final public const string ENTITY_NAME = 'payment_system_order_transaction';
}
