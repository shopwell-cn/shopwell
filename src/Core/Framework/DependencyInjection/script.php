<?php declare(strict_types=1);

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\App\Lifecycle\Persister\ScriptPersister;
use Shopwell\Core\Framework\Script\Api\AclFacadeHookFactory;
use Shopwell\Core\Framework\Script\Api\ScriptApiRoute;
use Shopwell\Core\Framework\Script\Api\ScriptResponseEncoder;
use Shopwell\Core\Framework\Script\Api\ScriptResponseFactoryFacadeHookFactory;
use Shopwell\Core\Framework\Script\Api\ScriptStoreApiRoute;
use Shopwell\Core\Framework\Script\AppContextCreator;
use Shopwell\Core\Framework\Script\Debugging\ScriptTraces;
use Shopwell\Core\Framework\Script\Execution\ScriptEnvironmentFactory;
use Shopwell\Core\Framework\Script\Execution\ScriptExecutor;
use Shopwell\Core\Framework\Script\Execution\ScriptLoader;
use Shopwell\Core\Framework\Script\ScriptDefinition;
use Shopwell\Core\System\SalesChannel\Api\StructEncoder;
use Shopwell\Storefront\Controller\ScriptController;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(ScriptLoader::class)
        ->args([
            service(Connection::class),
            service(ScriptPersister::class),
            service('cache.object'),
            '%twig.cache%',
            '%kernel.debug%',
        ])
        ->tag('kernel.event_subscriber');

    $services->set(ScriptExecutor::class)
        ->public()
        ->args([
            service(ScriptLoader::class),
            service(ScriptTraces::class),
            service('service_container'),
            service(ScriptEnvironmentFactory::class),
        ]);

    $services->set(ScriptEnvironmentFactory::class)
        ->public()
        ->args([
            service('twig.extension.debug'),
            tagged_iterator('shopwell.app_script.twig.extension'),
            '%kernel.shopwell_version%',
        ])
        ->tag('kernel.reset', ['method' => 'reset']);

    $services->set(ScriptDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(ScriptTraces::class)
        ->public()
        ->tag('data_collector')
        ->tag('kernel.reset', ['method' => 'reset']);

    $services->set(ScriptStoreApiRoute::class)
        ->public()
        ->args([
            service(ScriptExecutor::class),
            service(ScriptResponseEncoder::class),
            service('cache.object'),
            service('logger'),
        ]);

    $services->set(ScriptApiRoute::class)
        ->public()
        ->args([
            service(ScriptExecutor::class),
            service(ScriptLoader::class),
            service(ScriptResponseEncoder::class),
        ]);

    $services->set(ScriptResponseFactoryFacadeHookFactory::class)
        ->public()
        ->args([
            service('router'),
            service(ScriptController::class)->nullOnInvalid(),
        ]);

    $services->set(ScriptResponseEncoder::class)
        ->args([service(StructEncoder::class)]);

    $services->set(AclFacadeHookFactory::class)
        ->public()
        ->args([service(AppContextCreator::class)]);

    $services->set(AppContextCreator::class)
        ->args([service(Connection::class)]);
};
