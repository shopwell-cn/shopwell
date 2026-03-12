<?php declare(strict_types=1);

namespace Shopwell\Core\Payment\Provider\Alipay;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Struct\ArrayStruct;
use Shopwell\Core\Payment\Gateway\GatewayFactory;
use Shopwell\Core\Payment\Provider\Alipay\Action\ConvertPaymentAction;

#[Package('payment-system')]
class AlipayGatewayFactory extends GatewayFactory
{
    final public const string NAME = 'alipay';

    public function configureContainer(ArrayStruct $config): void
    {
        $config->set(self::ACTIONS, [
            ConvertPaymentAction::class,
        ]);
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
