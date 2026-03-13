<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\PaymentProcessing;

trait GatewayAwareTrait
{
    public GatewayInterface $gateway;
}
