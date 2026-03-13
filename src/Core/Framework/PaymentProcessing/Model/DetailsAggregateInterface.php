<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\PaymentProcessing\Model;

use Shopwell\Core\Framework\Log\Package;

#[Package('payment-system')]
interface DetailsAggregateInterface
{
    public object|array $details {
        get;
    }
}
