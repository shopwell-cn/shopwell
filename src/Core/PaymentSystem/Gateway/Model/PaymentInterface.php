<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Gateway\Model;

use Shopwell\Core\PaymentSystem\Gateway\Security\TokenAggregateInterface;
use Shopwell\Core\PaymentSystem\Order\PaymentOrderEntity;

interface PaymentInterface extends DetailsAggregateInterface, DetailsAwareInterface, TokenAggregateInterface
{
    public PaymentOrderEntity $order {
        get;
    }
}
