<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Shopwell\Core\Framework\Notification\NotificationBulkEntityExtension;
use Shopwell\Core\Framework\Notification\NotificationDefinition;
use Shopwell\Core\Framework\Notification\NotificationService;

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
