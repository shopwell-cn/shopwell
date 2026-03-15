<?php declare(strict_types=1);

use Doctrine\DBAL\Connection;
use Shopwell\Core\Finance\VirtualAsset\Subscriber\CustomerVirtualAssetSubscriber;
use Shopwell\Core\Finance\VirtualAsset\VirtualAssetEntity;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(VirtualAssetEntity::class)->tag('shopwell.entity');

    $services->set(CustomerVirtualAssetSubscriber::class)
        ->args([
            service(Connection::class),
        ])
        ->tag('kernel.event_subscriber');
};
