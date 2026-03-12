<?php declare(strict_types=1);

namespace Shopwell\Core\Payment\Gateway\Model;

use Shopwell\Core\Payment\Gateway\Security\TokenAggregateInterface;
use Shopwell\Core\Payment\Order\PaymentOrderEntity;

interface PaymentInterface extends DetailsAggregateInterface, DetailsAwareInterface, TokenAggregateInterface
{
    public PaymentOrderEntity $order {
        get;
    }
}
