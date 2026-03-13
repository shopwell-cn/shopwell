<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\PaymentProcessing\Registry;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\PaymentProcessing\GatewayFactoryInterface;
use Shopwell\Core\Framework\PaymentProcessing\PaymentProcessingException;

#[Package('framework')]
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
            throw PaymentProcessingException::gatewayFactoryNotFound($name);
        }

        return $this->gatewayFactories[$name];
    }
}
