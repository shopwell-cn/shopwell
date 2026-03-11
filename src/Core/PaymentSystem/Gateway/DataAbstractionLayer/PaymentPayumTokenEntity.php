<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Gateway\DataAbstractionLayer;

use Shopwell\Core\Framework\DataAbstractionLayer\Attribute\Entity;
use Shopwell\Core\Framework\DataAbstractionLayer\Entity as EntityStruct;
use Shopwell\Core\Framework\Log\Package;

#[Package('payment-system')]
#[Entity(PaymentPayumTokenEntity::ENTITY_NAME)]
class PaymentPayumTokenEntity extends EntityStruct
{
    final public const string ENTITY_NAME = 'payment_system_payum_token';
}
