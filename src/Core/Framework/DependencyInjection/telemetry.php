<?php declare(strict_types=1);

use Shopwell\Core\Framework\Telemetry\Metrics\Config\MetricConfigProvider;
use Shopwell\Core\Framework\Telemetry\Metrics\Config\TransportConfigProvider;
use Shopwell\Core\Framework\Telemetry\Metrics\Meter;
use Shopwell\Core\Framework\Telemetry\Metrics\Transport\TransportCollection;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(Meter::class)
        ->public()
        ->lazy()
        ->args([
            service(TransportCollection::class),
            service(MetricConfigProvider::class),
            service('logger'),
            '%env(APP_ENV)%',
        ]);

    $services->set(MetricConfigProvider::class)
        ->args(['%shopwell.telemetry.metrics.definitions%']);

    $services->set(TransportConfigProvider::class)
        ->args([service(MetricConfigProvider::class)]);

    $services->set(TransportCollection::class)
        ->lazy()
        ->args([
            tagged_iterator('shopwell.metric_transport_factory'),
            service(TransportConfigProvider::class),
        ])
        ->factory([TransportCollection::class, 'create']);
};
