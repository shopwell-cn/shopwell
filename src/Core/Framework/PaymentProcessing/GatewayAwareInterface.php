<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\PaymentProcessing;

use Shopwell\Core\Framework\Log\Package;

#[Package('framework')]
interface GatewayAwareInterface
{
    public GatewayInterface $gateway {
        set;
    }
}
