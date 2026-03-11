<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Gateway\Registry;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\PaymentSystem\Gateway\GatewayFactoryInterface;
use Shopwell\Core\PaymentSystem\Gateway\PaymentSystemGatewayException;

#[Package('payment-system')]
class GatewayFactoryRegistry
{
    /**
     * @var array<string,GatewayFactoryInterface>
     */
    public private(set) array $gatewayFactories;

    /**
     * @internal
     *
     * @param GatewayFactoryInterface[] $gatewayFactories
     */
    public function __construct(
        array $gatewayFactories = []
    ) {
        foreach ($gatewayFactories as $gatewayFactory) {
            $this->gatewayFactories[$gatewayFactory->getName()] = $gatewayFactory;
        }
    }

    public function getGatewayFactory(string $name): GatewayFactoryInterface
    {
        if (!isset($this->gatewayFactories[$name])) {
            throw PaymentSystemGatewayException::gatewayFactoryNotFound($name);
        }

        return $this->gatewayFactories[$name];
    }
}
