<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Shopwell\Storefront\Event\StorefrontRenderEvent;
use Shopwell\Storefront\System\SalesChannel\SalesChannelAnalyticsLoader;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(SalesChannelAnalyticsLoader::class)
        ->args([service('sales_channel_analytics.repository')])
        ->tag('kernel.event_listener', ['event' => StorefrontRenderEvent::class, 'method' => 'loadAnalytics', 'priority' => 2000]);
};
