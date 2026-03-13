<?php declare(strict_types=1);

use Shopwell\Core\Framework\PaymentProcessing\Action\CapturePaymentAction;
use Shopwell\Core\Framework\PaymentProcessing\Extension\EndlessCycleDetectorExtension;
use Shopwell\Core\Framework\PaymentProcessing\Extension\EventDispatcherExtension;
use Shopwell\Core\Framework\PaymentProcessing\PaymentTypeRegistry;
use Shopwell\Core\Framework\PaymentProcessing\Registry\GatewayFactoryRegistry;
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
};
