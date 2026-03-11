<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Order;

use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\Entity;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\Field;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\FieldType;
use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\PrimaryKey;
use Shopwell\Core\Framework\DataAbstractionLayer\Entity as EntityStruct;
use Shopwell\Core\Framework\Log\Package;

#[Package('payment-system')]
#[Entity(PaymentOrderEntity::ENTITY_NAME, collectionClass: PaymentOrderCollection::class)]
class PaymentOrderEntity extends EntityStruct
{
    final public const string ENTITY_NAME = 'payment_system_order';

    #[PrimaryKey]
    #[Field(type: FieldType::UUID, api: true)]
    public string $id;

    #[Field(type: FieldType::STRING, api: true)]
    public string $paymentOrderNumber;

    #[Field(type: FieldType::STRING, api: true)]
    public string $outOrderNo;

    #[Field(type: FieldType::FLOAT, api: true)]
    public string $amount;

    #[Field(type: FieldType::STRING, api: true)]
    public string $subject;

    #[Field(type: FieldType::STRING, api: true)]
    public string $currency;

    #[Field(type: FieldType::STRING, api: true, )]
    public ?string $body = null;

    #[Field(type: FieldType::STRING, api: true)]
    public ?string $returnUrl = null;

    #[Field(type: FieldType::STRING, api: true)]
    public ?string $notifyUrl = null;

    #[Field(type: FieldType::JSON, api: true)]
    public ?array $extraParam = null;

    #[Field(type: FieldType::JSON, api: true)]
    public ?array $attach = null;

    #[Field(type: FieldType::DATETIME, api: true)]
    public ?\DateTimeImmutable $timeExpire = null;
}
