<?php declare(strict_types=1);

namespace Shopwell\Core\Payment\Gateway;

trait GatewayAwareTrait
{
    public GatewayInterface $gateway;
}
