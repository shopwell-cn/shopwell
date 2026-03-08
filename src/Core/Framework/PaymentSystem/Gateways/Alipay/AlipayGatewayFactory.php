<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\PaymentSystem\Gateways\Alipay;

use Payum\Core\GatewayFactory;
use Shopwell\Core\Framework\Log\Package;

#[Package('framework')]
class AlipayGatewayFactory extends GatewayFactory
{
}
