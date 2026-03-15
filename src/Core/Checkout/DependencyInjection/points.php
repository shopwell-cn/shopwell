<?php declare(strict_types=1);

use Doctrine\DBAL\Connection;
use Shopwell\Core\Checkout\Points\PointsEntity;
use Shopwell\Core\Checkout\Points\Subscriber\CustomerPointsSubscriber;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(PointsEntity::class)->tag('shopwell.entity');

    $services->set(CustomerPointsSubscriber::class)
        ->args([
            service(Connection::class),
        ])
        ->tag('kernel.event_subscriber');
};
