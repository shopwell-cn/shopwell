<?php declare(strict_types=1);

use Shopwell\Core\Payment\Gateway\Action\CapturePaymentAction;
use Shopwell\Core\Payment\Gateway\DataAbstractionLayer\GatewayConfigEntity;
use Shopwell\Core\Payment\Gateway\DataAbstractionLayer\PaymentTokenEntity;
use Shopwell\Core\Payment\Gateway\Extension\EndlessCycleDetectorExtension;
use Shopwell\Core\Payment\Gateway\Extension\EventDispatcherExtension;
use Shopwell\Core\Payment\Gateway\Payment;
use Shopwell\Core\Payment\Gateway\PaymentTypeRegistry;
use Shopwell\Core\Payment\Gateway\Registry\DynamicRegistry;
use Shopwell\Core\Payment\Gateway\Registry\GatewayFactoryRegistry;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(PaymentTypeRegistry::class)
        ->args([
            param('shopwell.payment.allowed_types'),
        ]);

    $services->set(GatewayConfigEntity::class)->tag('shopwell.entity');
    $services->set(PaymentTokenEntity::class)->tag('shopwell.entity');

    $services->set(EndlessCycleDetectorExtension::class)->public();
    $services->set(CapturePaymentAction::class)->public();

    $services->set(EventDispatcherExtension::class)
        ->public()
        ->args([
            service('event_dispatcher'),
        ]);

    $services->set(GatewayFactoryRegistry::class)
        ->args([
            tagged_iterator('payment.gateway_factory'),
        ]);

    $services->set(DynamicRegistry::class)
        ->args([
            service(GatewayFactoryRegistry::class),
            service('payment_gateway_config.repository'),
        ]);

    $services->set(Payment::class)
        ->public()
        ->args([
            service(DynamicRegistry::class),
        ]);
};
