<?php declare(strict_types=1);

namespace Shopwell\Core\Payment\Gateway;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Payment\Gateway\Registry\RegistryInterface;

#[Package('payment-system')]
class Payment implements RegistryInterface
{
    public array $gateways {
        get {
            return $this->registry->gateways;
        }
    }

    public function __construct(
        protected readonly RegistryInterface $registry
    ) {
    }

    public function getGatewayFactory(string $name): GatewayFactoryInterface
    {
        return $this->registry->getGatewayFactory($name);
    }

    public function getGatewayFactories(): array
    {
        return $this->registry->getGatewayFactories();
    }

    public function getGateway(string $name): GatewayInterface
    {
        return $this->registry->getGateway($name);
    }
}
