<?php declare(strict_types=1);

use Shopwell\Core\PaymentSystem\Provider\Alipay\AlipayGatewayFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(AlipayGatewayFactory::class)
        ->tag('payment_system.gateway_factory', ['factory' => 'alipay']);
};
