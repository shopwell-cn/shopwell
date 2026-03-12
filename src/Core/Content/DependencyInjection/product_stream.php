<?php declare(strict_types=1);

use Doctrine\DBAL\Connection;
use Shopwell\Core\Content\Product\ProductDefinition;
use Shopwell\Core\Content\ProductStream\Aggregate\ProductStreamFilter\ProductStreamFilterDefinition;
use Shopwell\Core\Content\ProductStream\Aggregate\ProductStreamTranslation\ProductStreamTranslationDefinition;
use Shopwell\Core\Content\ProductStream\DataAbstractionLayer\ProductStreamIndexer;
use Shopwell\Core\Content\ProductStream\ProductStreamDefinition;
use Shopwell\Core\Content\ProductStream\ScheduledTask\UpdateProductStreamMappingTask;
use Shopwell\Core\Content\ProductStream\ScheduledTask\UpdateProductStreamMappingTaskHandler;
use Shopwell\Core\Content\ProductStream\Service\ProductStreamBuilder;
use Shopwell\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(ProductStreamDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(ProductStreamTranslationDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(ProductStreamFilterDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(ProductStreamBuilder::class)
        ->public()
        ->args([
            service('product_stream.repository'),
            service(ProductDefinition::class),
        ]);

    $services->set(ProductStreamIndexer::class)
        ->args([
            service(Connection::class),
            service(IteratorFactory::class),
            service('product_stream.repository'),
            service('serializer'),
            service(ProductDefinition::class),
            service('event_dispatcher'),
        ])
        ->tag('shopwell.entity_indexer', ['priority' => 100]);

    $services->set(UpdateProductStreamMappingTask::class)
        ->tag('shopwell.scheduled.task');

    $services->set(UpdateProductStreamMappingTaskHandler::class)
        ->args([
            service('scheduled_task.repository'),
            service('logger'),
            service('product_stream.repository'),
        ])
        ->tag('messenger.message_handler');
};
