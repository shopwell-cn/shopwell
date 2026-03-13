<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\PaymentProcessing;

use Shopwell\Core\Framework\Log\Package;

#[Package('framework')]
interface GatewayFactoryInterface
{
    public function create(array $config): Gateway;

    public function getName(): string;
}
