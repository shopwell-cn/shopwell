<?php declare(strict_types=1);

use Shopwell\Storefront\Event\StorefrontRenderEvent;
use Shopwell\Storefront\System\SalesChannel\SalesChannelAnalyticsLoader;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(SalesChannelAnalyticsLoader::class)
        ->args([service('sales_channel_analytics.repository')])
        ->tag('kernel.event_listener', ['event' => StorefrontRenderEvent::class, 'method' => 'loadAnalytics', 'priority' => 2000]);
};
