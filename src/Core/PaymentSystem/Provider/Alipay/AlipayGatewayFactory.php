<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Provider\Alipay;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Struct\ArrayStruct;
use Shopwell\Core\PaymentSystem\Gateway\GatewayFactory;
use Shopwell\Core\PaymentSystem\Provider\Alipay\Action\ConvertPaymentAction;

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
