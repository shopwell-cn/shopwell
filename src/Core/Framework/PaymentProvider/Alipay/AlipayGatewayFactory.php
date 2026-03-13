<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\PaymentProvider\Alipay;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\PaymentProcessing\GatewayFactory;
use Shopwell\Core\Framework\Struct\ArrayStruct;

#[Package('payment-system')]
class AlipayGatewayFactory extends GatewayFactory
{
    final public const string NAME = 'alipay';

    public function configureContainer(ArrayStruct $config): void
    {
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
