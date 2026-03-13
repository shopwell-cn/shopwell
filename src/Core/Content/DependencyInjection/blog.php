<?php declare(strict_types=1);

use Shopwell\Core\Content\Blog\BlogEntity;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(BlogEntity::class)->tag('shopwell.entity');
};
