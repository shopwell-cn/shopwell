<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Gateway;

trait GatewayAwareTrait
{
    public GatewayInterface $gateway;
}
