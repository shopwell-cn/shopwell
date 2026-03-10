<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Gateway\Provider\Alipay;

use Payum\Core\GatewayFactory;
use Shopwell\Core\Framework\Log\Package;

#[Package('payment-system')]
class AlipayGatewayFactory extends GatewayFactory
{
}
