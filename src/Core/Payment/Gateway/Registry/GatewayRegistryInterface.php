<?php declare(strict_types=1);

namespace Shopwell\Core\Payment\Gateway\Registry;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Payment\Gateway\GatewayInterface;

#[Package('payment-system')]
interface GatewayRegistryInterface
{
    public array $gateways {
        get;
    }

    public function getGateway(string $name): GatewayInterface;
}
