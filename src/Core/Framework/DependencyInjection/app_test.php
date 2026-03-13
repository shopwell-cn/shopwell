<?php declare(strict_types=1);

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\App\AppStateService;
use Shopwell\Core\Framework\App\DeletedApps\DeletedAppsGateway;
use Shopwell\Core\Framework\App\Lifecycle\AppLifecycle;
use Shopwell\Core\Framework\App\Lifecycle\PermissionLifecycleService;
use Shopwell\Core\Framework\App\Lifecycle\Registration\AppRegistrationService;
use Shopwell\Core\Framework\App\Source\NoDatabaseSourceResolver;
use Shopwell\Core\Framework\App\Source\SourceResolver;
use Shopwell\Core\Framework\App\Validation\ConfigValidator;
use Shopwell\Core\Framework\Plugin\Util\AssetService;
use Shopwell\Core\Framework\Script\Execution\ScriptExecutor;
use Shopwell\Core\System\CustomEntity\CustomEntityLifecycleService;
use Shopwell\Core\System\CustomEntity\Schema\CustomEntitySchemaUpdater;
use Shopwell\Core\System\SystemConfig\SystemConfigService;
use Shopwell\Core\System\SystemConfig\Util\ConfigReader;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('app-life-cycle-dev', AppLifecycle::class)
        ->public()
        ->args([
            tagged_iterator('shopwell.app_lifecycle.persister'),
            service('app.repository'),
            service(PermissionLifecycleService::class),
            service('event_dispatcher'),
            service(AppRegistrationService::class),
            service(AppStateService::class),
            service('language.repository'),
            service(SystemConfigService::class),
            service(ConfigValidator::class),
            service('integration.repository'),
            service('acl_role.repository'),
            service(AssetService::class),
            service(ScriptExecutor::class),
            '%kernel.project_dir%',
            service(Connection::class),
            service(CustomEntitySchemaUpdater::class),
            service(CustomEntityLifecycleService::class),
            '%kernel.shopwell_version%',
            'dev',
            service('custom_entity.repository'),
            service(SourceResolver::class),
            service(ConfigReader::class),
            service(DeletedAppsGateway::class),
        ]);

    $services->set(SourceResolver::class)
        ->args([
            tagged_iterator('app.source_resolver'),
            service('app.repository'),
            service(NoDatabaseSourceResolver::class),
        ])
        ->tag('kernel.reset', ['method' => 'reset']);
};
