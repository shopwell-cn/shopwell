<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Increment;

use Shopwell\Core\Framework\Log\Package;

/**
 * @final
 */
#[Package('framework')]
class IncrementGatewayRegistry
{
    /**
     * @param AbstractIncrementer[] $gateways
     */
    public function __construct(private readonly iterable $gateways)
    {
    }

    public function get(string $pool): AbstractIncrementer
    {
        foreach ($this->gateways as $gateway) {
            if ($gateway->getPool() === $pool) {
                return $gateway;
            }
        }

        throw IncrementException::gatewayNotFound($pool);
    }
}
