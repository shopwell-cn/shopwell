<?php declare(strict_types=1);

use Shopwell\Core\PaymentSystem\Provider\Alipay\AlipayGatewayFactory;
use Shopwell\Core\PaymentSystem\Provider\WeChat\WechatGatewayFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(AlipayGatewayFactory::class)
        ->tag('payment_system.gateway_factory');

    $services->set(WechatGatewayFactory::class)
        ->tag('payment_system.gateway_factory');
};
