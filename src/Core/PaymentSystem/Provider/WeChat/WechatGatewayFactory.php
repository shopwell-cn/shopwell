<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Provider\WeChat;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\PaymentSystem\Gateway\GatewayFactory;

#[Package('payment-system')]
class WechatGatewayFactory extends GatewayFactory
{
    final public const string NAME = 'wechat';

    public function getName(): string
    {
        return self::NAME;
    }
}
