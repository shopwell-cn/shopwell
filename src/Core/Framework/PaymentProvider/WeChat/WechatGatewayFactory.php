<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\PaymentProvider\WeChat;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\PaymentProcessing\GatewayFactory;
use Shopwell\Core\Framework\Struct\ArrayStruct;

#[Package('payment-system')]
class WechatGatewayFactory extends GatewayFactory
{
    final public const string NAME = 'wechat';

    public function configureContainer(ArrayStruct $config): void
    {
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
