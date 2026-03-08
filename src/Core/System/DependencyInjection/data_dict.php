<?php declare(strict_types=1);
/**
 * @codeCoverageIgnore - DI wiring only
 */

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory;
use Shopwell\Core\Framework\DataAbstractionLayer\Indexing\ChildCountUpdater;
use Shopwell\Core\Framework\DataAbstractionLayer\Indexing\TreeUpdater;
use Shopwell\Core\System\DataDict\Aggregate\DataDictGroupTranslation\DataDictGroupTranslationDefinition;
use Shopwell\Core\System\DataDict\Aggregate\DataDictItemTranslation\DataDictItemTranslationDefinition;
use Shopwell\Core\System\DataDict\CachedDataDictLoader;
use Shopwell\Core\System\DataDict\DataAbstractionLayer\DataDictItemIndexer;
use Shopwell\Core\System\DataDict\DataDictGroupDefinition;
use Shopwell\Core\System\DataDict\DataDictLoader;
use Shopwell\Core\System\DataDict\DataDictService;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(DataDictGroupDefinition::class)->tag('shopwell.entity.definition');
    $services->set(DataDictGroupTranslationDefinition::class)->tag('shopwell.entity.definition');
    $services->set(DataDictItemTranslationDefinition::class)->tag('shopwell.entity.definition');
    $services->set(DataDictGroupTranslationDefinition::class)->tag('shopwell.entity.definition');

    $services->set(DataDictItemIndexer::class)
        ->args([
            service(Connection::class),
            service(IteratorFactory::class),
            service('data_dict_item.repository'),
            service(ChildCountUpdater::class),
            service(TreeUpdater::class),
            service('event_dispatcher'),
            service('messenger.default_bus'),
        ]);

    $services->set(DataDictLoader::class)
        ->args([
            service('data_dict_group.repository'),
        ]);

    $services->set(CachedDataDictLoader::class)
        ->decorate(DataDictLoader::class)
        ->args([
            service(CachedDataDictLoader::class . '.inner'),
            service('cache.object'),
        ]);

    $services->set(DataDictService::class)
        ->args([
            service(DataDictLoader::class),
        ]);
};
