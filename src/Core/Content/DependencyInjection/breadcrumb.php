<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Shopwell\Core\Content\Breadcrumb\SalesChannel\BreadcrumbRoute;
use Shopwell\Core\Content\Category\Service\CategoryBreadcrumbBuilder;
use Shopwell\Core\Framework\Adapter\Cache\CacheTagCollector;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(BreadcrumbRoute::class)
        ->public()
        ->args([
            service(CategoryBreadcrumbBuilder::class),
            service(CacheTagCollector::class),
        ]);
};
