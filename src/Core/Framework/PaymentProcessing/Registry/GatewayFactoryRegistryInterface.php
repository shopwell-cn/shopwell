<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\PaymentProcessing\Registry;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\PaymentProcessing\GatewayFactoryInterface;

#[Package('payment-system')]
interface GatewayFactoryRegistryInterface
{
    public function getGatewayFactory(string $name): GatewayFactoryInterface;

    /**
     * The key must be a gateway factory name
     *
     * @return array<string,GatewayFactoryInterface>
     */
    public function getGatewayFactories(): array;
}
