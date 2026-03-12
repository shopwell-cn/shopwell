<?php declare(strict_types=1);

use Shopwell\Core\Framework\Notification\NotificationBulkEntityExtension;
use Shopwell\Core\Framework\Notification\NotificationDefinition;
use Shopwell\Core\Framework\Notification\NotificationService;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(NotificationBulkEntityExtension::class)
        ->tag('shopwell.bulk.entity.extension');

    $services->set(NotificationService::class)
        ->public()
        ->args([service('notification.repository')]);

    $services->set(NotificationDefinition::class)
        ->tag('shopwell.entity.definition');
};
