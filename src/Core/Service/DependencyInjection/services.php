<?php declare(strict_types=1);

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\App\AppExtractor;
use Shopwell\Core\Framework\App\AppStateService;
use Shopwell\Core\Framework\App\Lifecycle\AppLifecycle;
use Shopwell\Core\Framework\App\Manifest\ManifestFactory;
use Shopwell\Core\Framework\App\Payload\AppPayloadServiceHelper;
use Shopwell\Core\Framework\App\Privileges\Privileges;
use Shopwell\Core\Framework\App\ShopId\ShopIdProvider;
use Shopwell\Core\Framework\Notification\NotificationService;
use Shopwell\Core\Framework\Store\Services\AbstractExtensionDataProvider;
use Shopwell\Core\Service\AllServiceInstaller;
use Shopwell\Core\Service\Api\PermissionController;
use Shopwell\Core\Service\Api\ServiceController;
use Shopwell\Core\Service\Command\Install;
use Shopwell\Core\Service\LifecycleManager;
use Shopwell\Core\Service\MessageHandler\InstallServicesHandler;
use Shopwell\Core\Service\MessageHandler\LogConsentToRegistryHandler;
use Shopwell\Core\Service\MessageHandler\UpdateServiceHandler;
use Shopwell\Core\Service\Notification;
use Shopwell\Core\Service\Permission\PermissionsService;
use Shopwell\Core\Service\Requirement\RequirementsValidator;
use Shopwell\Core\Service\Requirement\ServiceConsentRequirement;
use Shopwell\Core\Service\Requirement\ShopwellAccountRequirement;
use Shopwell\Core\Service\ScheduledTask\InstallServicesTask;
use Shopwell\Core\Service\ScheduledTask\InstallServicesTaskHandler;
use Shopwell\Core\Service\ServiceClientFactory;
use Shopwell\Core\Service\ServiceLifecycle;
use Shopwell\Core\Service\ServiceRegistry\Client;
use Shopwell\Core\Service\ServiceRegistry\PermissionLogger;
use Shopwell\Core\Service\ServiceSourceResolver;
use Shopwell\Core\Service\Subscriber\ExtensionCompatibilitiesResolvedSubscriber;
use Shopwell\Core\Service\Subscriber\InstalledExtensionsListingLoadedSubscriber;
use Shopwell\Core\Service\Subscriber\LicenseSyncSubscriber;
use Shopwell\Core\Service\Subscriber\PermissionsSubscriber;
use Shopwell\Core\Service\Subscriber\ServiceLifecycleSubscriber;
use Shopwell\Core\Service\Subscriber\ShopwellAccountSubscriber;
use Shopwell\Core\Service\Subscriber\SystemUpdateSubscriber;
use Shopwell\Core\Service\TemporaryDirectoryFactory;
use Shopwell\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('env(SERVICE_REGISTRY_URL)', 'https://registry.services.shopwell.io');
    $parameters->set('env(ENABLE_SERVICES)', 'auto');

    $services->set(ServiceController::class)
        ->public()
        ->args([
            service('app.repository'),
            service('messenger.default_bus'),
            service(AppStateService::class),
            service(AppLifecycle::class),
            service(LifecycleManager::class),
        ]);

    $services->set(PermissionController::class)
        ->public()
        ->args([service(PermissionsService::class)]);

    $services->set(Install::class)
        ->args([service(LifecycleManager::class)])
        ->tag('console.command');

    $services->set(Client::class)
        ->args([
            '%env(SERVICE_REGISTRY_URL)%',
            '%env(APP_URL)%',
            service('http_client'),
        ])
        ->tag('kernel.reset', ['method' => 'reset']);

    $services->set(ServiceLifecycle::class)
        ->args([
            service(Client::class),
            service(ServiceClientFactory::class),
            service(AppLifecycle::class),
            service('app.repository'),
            service('logger'),
            service(ManifestFactory::class),
            service(ServiceSourceResolver::class),
            service(AppStateService::class),
            service('event_dispatcher'),
            service(RequirementsValidator::class),
        ]);

    $services->set(ServiceClientFactory::class)
        ->args([
            service(HttpClientInterface::class),
            service(Client::class),
            '%kernel.shopwell_version%',
            service('shopwell.app_system.guzzle.middleware'),
            service(AppPayloadServiceHelper::class),
        ]);

    $services->set(AllServiceInstaller::class)
        ->args([
            service(Client::class),
            service(ServiceLifecycle::class),
            service('app.repository'),
            service('messenger.bus.default'),
            service('event_dispatcher'),
        ]);

    $services->set(InstallServicesTask::class)
        ->tag('shopwell.scheduled.task');

    $services->set(InstallServicesTaskHandler::class)
        ->args([
            service('scheduled_task.repository'),
            service('logger'),
            service(LifecycleManager::class),
        ])
        ->tag('messenger.message_handler');

    $services->set(UpdateServiceHandler::class)
        ->args([service(ServiceLifecycle::class)])
        ->tag('messenger.message_handler');

    $services->set(InstallServicesHandler::class)
        ->args([service(LifecycleManager::class)])
        ->tag('messenger.message_handler');

    $services->set(ServiceSourceResolver::class)
        ->args([
            service(Client::class),
            service(TemporaryDirectoryFactory::class),
            service(AppExtractor::class),
            service(Filesystem::class),
        ])
        ->tag('app.source_resolver', ['priority' => 100]);

    $services->set(ExtensionCompatibilitiesResolvedSubscriber::class)
        ->args([
            service(Client::class),
            service(AbstractExtensionDataProvider::class),
        ])
        ->tag('kernel.event_subscriber');

    $services->set(InstalledExtensionsListingLoadedSubscriber::class)
        ->args([service('app.repository')])
        ->tag('kernel.event_subscriber');

    $services->set(SystemUpdateSubscriber::class)
        ->args([
            service(LifecycleManager::class),
            service('logger'),
        ])
        ->tag('kernel.event_subscriber');

    $services->set(TemporaryDirectoryFactory::class)
        ->args(['%kernel.project_dir%']);

    $services->set(LicenseSyncSubscriber::class)
        ->args([
            service(SystemConfigService::class),
            service(Client::class),
            service('app.repository'),
            service('logger'),
            service(ServiceClientFactory::class),
        ])
        ->tag('kernel.event_subscriber');

    $services->set(PermissionsService::class)
        ->args([
            service(SystemConfigService::class),
            service('event_dispatcher'),
            service(PermissionLogger::class),
        ]);

    $services->set(ServiceConsentRequirement::class)
        ->args([service(PermissionsService::class)])
        ->tag('shopwell.service.requirement');

    $services->set(ShopwellAccountRequirement::class)
        ->args([service(Connection::class)])
        ->tag('shopwell.service.requirement');

    $services->set(RequirementsValidator::class)
        ->args([tagged_iterator('shopwell.service.requirement', defaultIndexMethod: 'getName')]);

    $services->set(LifecycleManager::class)
        ->args([
            '%env(ENABLE_SERVICES)%',
            '%kernel.environment%',
            service(Privileges::class),
            service(SystemConfigService::class),
            service('app.repository'),
            service(AppLifecycle::class),
            service(AllServiceInstaller::class),
            service(PermissionsService::class),
            service(Client::class),
            service(RequirementsValidator::class),
        ]);

    $services->set(ServiceLifecycleSubscriber::class)
        ->args([
            service(LifecycleManager::class),
            service(Notification::class),
        ])
        ->tag('kernel.event_subscriber');

    $services->set(PermissionsSubscriber::class)
        ->args([service(LifecycleManager::class)])
        ->tag('kernel.event_subscriber');

    $services->set(ShopwellAccountSubscriber::class)
        ->args([service(LifecycleManager::class)])
        ->tag('kernel.event_subscriber');

    $services->set(Notification::class)
        ->args([service(NotificationService::class)]);

    $services->set(PermissionLogger::class)
        ->args([
            service(Client::class),
            service('messenger.bus.default'),
            service(ShopIdProvider::class),
            service(SystemConfigService::class),
        ]);

    $services->set(LogConsentToRegistryHandler::class)
        ->args([service(PermissionLogger::class)])
        ->tag('messenger.message_handler');
};
