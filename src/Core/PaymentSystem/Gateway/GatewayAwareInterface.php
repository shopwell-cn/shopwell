<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Gateway;

use Shopwell\Core\Framework\Log\Package;

#[Package('payment-system')]
interface GatewayAwareInterface
{
    public function setGateway(GatewayInterface $gateway);
}
