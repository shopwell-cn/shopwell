<?php declare(strict_types=1);

use Shopwell\Core\Framework\PaymentProvider\Alipay\AlipayGatewayFactory;
use Shopwell\Core\Framework\PaymentProvider\WeChat\WechatGatewayFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(AlipayGatewayFactory::class)
        ->call('setContainer', [service('service_container')])
        ->tag('payment_system.gateway_factory');

    $services->set(WechatGatewayFactory::class)
        ->call('setContainer', [service('service_container')])
        ->tag('payment_system.gateway_factory');
};
