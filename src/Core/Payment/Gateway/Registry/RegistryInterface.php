<?php declare(strict_types=1);

namespace Shopwell\Core\Payment\Gateway\Registry;

use Shopwell\Core\Framework\Log\Package;

#[Package('payment-system')]
interface RegistryInterface extends GatewayRegistryInterface, GatewayFactoryRegistryInterface
{
}
