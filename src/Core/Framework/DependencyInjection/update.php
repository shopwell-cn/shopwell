<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Shopwell\Core\Framework\Notification\NotificationService;
use Shopwell\Core\Framework\Store\Services\AbstractExtensionDataProvider;
use Shopwell\Core\Framework\Store\Services\ExtensionLifecycleService;
use Shopwell\Core\Framework\Store\Services\StoreClient;
use Shopwell\Core\Framework\Update\Api\UpdateController;
use Shopwell\Core\Framework\Update\Checkers\LicenseCheck;
use Shopwell\Core\Framework\Update\Checkers\WriteableCheck;
use Shopwell\Core\Framework\Update\Services\ApiClient;
use Shopwell\Core\Framework\Update\Services\ExtensionCompatibility;
use Shopwell\Core\Framework\Update\Services\Filesystem;
use Shopwell\Core\Framework\Update\Services\UpdateHtaccess;
use Shopwell\Core\Framework\Update\Subscriber\UpdateSubscriber;
use Shopwell\Core\System\SystemConfig\SystemConfigService;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('env(SHOPWELL_DISABLE_UPDATE_CHECK)', '');

    $services->set(UpdateController::class)
        ->public()
        ->args([
            service(ApiClient::class),
            service(WriteableCheck::class),
            service(LicenseCheck::class),
            service(ExtensionCompatibility::class),
            service('event_dispatcher'),
            service(SystemConfigService::class),
            service(ExtensionLifecycleService::class),
            '%kernel.shopwell_version%',
            '%env(bool:SHOPWELL_DISABLE_UPDATE_CHECK)%',
        ])
        ->call('setContainer', [service('service_container')]);

    $services->set(ApiClient::class)
        ->args([
            service('http_client'),
            '%shopwell.auto_update.enabled%',
            '%kernel.shopwell_version%',
            '%kernel.project_dir%',
        ]);

    $services->set(ExtensionCompatibility::class)
        ->args([
            service(StoreClient::class),
            service(AbstractExtensionDataProvider::class),
            service('event_dispatcher'),
        ]);

    $services->set(Filesystem::class);

    $services->set(WriteableCheck::class)
        ->args([
            service(Filesystem::class),
            '%kernel.project_dir%',
        ])
        ->tag('shopwell.update_api.checker', ['priority' => 3]);

    $services->set(LicenseCheck::class)
        ->args([
            service(SystemConfigService::class),
            service(StoreClient::class),
        ])
        ->tag('shopwell.update_api.checker', ['priority' => 4]);

    $services->set(UpdateHtaccess::class)
        ->args(['%kernel.project_dir%/public/.htaccess'])
        ->tag('kernel.event_subscriber');

    $services->set(UpdateSubscriber::class)
        ->args([service(NotificationService::class)])
        ->tag('kernel.event_subscriber');
};
