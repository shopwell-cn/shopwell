<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Gateway\Registry;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\PaymentSystem\Gateway\GatewayInterface;

#[Package('payment-system')]
interface GatewayRegistryInterface
{
    public array $gateways {
        get;
    }

    public function getGateway(string $name): GatewayInterface;
}
