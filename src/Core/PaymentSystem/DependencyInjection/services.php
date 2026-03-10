<?php declare(strict_types=1);

use Shopwell\Core\PaymentSystem\Framework\PaymentTypeRegistry;
use Shopwell\Core\PaymentSystem\Framework\Provider\Alipay\AlipayGatewayFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

return function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(AlipayGatewayFactory::class)
        ->tag('payum.gateway_factory', ['factory' => 'alipay']);

    $services->set(PaymentTypeRegistry::class)
        ->args([
            param('shopwell.payment.allowed_types'),
        ]);
};
