<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Order\Aggregate\PaymentOrderTransaction;

use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\CustomFields;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\Entity;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\Field;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\FieldType;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\ForeignKey;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\ManyToOne;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\OnDelete;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\PrimaryKey;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\State;
use Shopwell\Core\Framework\DataAbstractionLayer\Entity as EntityStruct;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\PaymentSystem\Order\PaymentOrderEntity;

#[Package('payment-system')]
#[Entity(PaymentOrderTransactionEntity::ENTITY_NAME, collectionClass: PaymentOrderTransactionCollection::class)]
class PaymentOrderTransactionEntity extends EntityStruct
{
    use EntityCustomFieldsTrait;

    final public const string ENTITY_NAME = 'payment_order_transaction';

    #[PrimaryKey]
    #[Field(type: FieldType::UUID, api: true)]
    public string $id;

    #[ForeignKey(entity: 'payment_order', api: true)]
    public string $orderId;

    #[ManyToOne(entity: 'payment_order', onDelete: OnDelete::CASCADE, api: true)]
    public ?PaymentOrderEntity $order = null;

    #[State(machine: PaymentOrderTransactionStates::STATE_MACHINE, api: true)]
    public string $stateId;

    #[Field(type: FieldType::FLOAT, api: true)]
    public string $amount;

    /**
     * @var array<mixed>|null
     */
    #[CustomFields(true)]
    protected ?array $customFields = null;
}
