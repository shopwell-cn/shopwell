<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\PaymentProcessing;

use Shopwell\Core\Framework\Log\Package;

#[Package('payment-system')]
interface GatewayAwareInterface
{
    public GatewayInterface $gateway {
        set;
    }
}
