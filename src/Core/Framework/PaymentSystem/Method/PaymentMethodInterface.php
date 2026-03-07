<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\PaymentSystem\Method;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\PaymentSystem\Gateway\GatewayInterface;

#[Package('framework')]
interface PaymentMethodInterface
{
    public function createConfig(array $config = []): array;

    public function create(array $config = []): GatewayInterface;
}
