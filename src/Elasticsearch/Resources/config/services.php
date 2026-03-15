<?php declare(strict_types=1);

use Doctrine\DBAL\Connection;
use OpenSearch\Client;
use Shopwell\Core\Content\Product\DataAbstractionLayer\SearchKeywordUpdater;
use Shopwell\Core\Content\Product\ProductDefinition;
use Shopwell\Core\Content\Product\SearchKeyword\ProductSearchBuilderInterface;
use Shopwell\Core\Framework\Adapter\Storage\AbstractKeyValueStorage;
use Shopwell\Core\Framework\Api\Serializer\JsonEntityEncoder;
use Shopwell\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory;
use Shopwell\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use Shopwell\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\EntityAggregatorInterface;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\EntitySearcherInterface;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\SearchConfigLoader;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Term\Tokenizer;
use Shopwell\Core\System\CustomField\CustomFieldService;
use Shopwell\Core\System\Language\LanguageLoader;
use Shopwell\Core\System\Language\SalesChannelLanguageLoader;
use Shopwell\Elasticsearch\Admin\AdminElasticsearchEntitySearcher;
use Shopwell\Elasticsearch\Admin\AdminElasticsearchHelper;
use Shopwell\Elasticsearch\Admin\AdminSearchController;
use Shopwell\Elasticsearch\Admin\AdminSearcher;
use Shopwell\Elasticsearch\Admin\AdminSearchRegistry;
use Shopwell\Elasticsearch\Admin\Indexer\CategoryAdminSearchIndexer;
use Shopwell\Elasticsearch\Admin\Indexer\CustomerAdminSearchIndexer;
use Shopwell\Elasticsearch\Admin\Indexer\CustomerGroupAdminSearchIndexer;
use Shopwell\Elasticsearch\Admin\Indexer\LandingPageAdminSearchIndexer;
use Shopwell\Elasticsearch\Admin\Indexer\ManufacturerAdminSearchIndexer;
use Shopwell\Elasticsearch\Admin\Indexer\MediaAdminSearchIndexer;
use Shopwell\Elasticsearch\Admin\Indexer\OrderAdminSearchIndexer;
use Shopwell\Elasticsearch\Admin\Indexer\PaymentMethodAdminSearchIndexer;
use Shopwell\Elasticsearch\Admin\Indexer\ProductAdminSearchIndexer;
use Shopwell\Elasticsearch\Admin\Indexer\ProductStreamAdminSearchIndexer;
use Shopwell\Elasticsearch\Admin\Indexer\PromotionAdminSearchIndexer;
use Shopwell\Elasticsearch\Admin\Indexer\PropertyGroupAdminSearchIndexer;
use Shopwell\Elasticsearch\Admin\Indexer\SalesChannelAdminSearchIndexer;
use Shopwell\Elasticsearch\Admin\Indexer\ShippingMethodAdminSearchIndexer;
use Shopwell\Elasticsearch\Admin\Subscriber\RefreshIndexSubscriber;
use Shopwell\Elasticsearch\Framework\ClientFactory;
use Shopwell\Elasticsearch\Framework\Command\ElasticsearchAdminIndexingCommand;
use Shopwell\Elasticsearch\Framework\Command\ElasticsearchAdminResetCommand;
use Shopwell\Elasticsearch\Framework\Command\ElasticsearchAdminTestCommand;
use Shopwell\Elasticsearch\Framework\Command\ElasticsearchAdminUpdateMappingCommand;
use Shopwell\Elasticsearch\Framework\Command\ElasticsearchCleanIndicesCommand;
use Shopwell\Elasticsearch\Framework\Command\ElasticsearchCreateAliasCommand;
use Shopwell\Elasticsearch\Framework\Command\ElasticsearchIndexingCommand;
use Shopwell\Elasticsearch\Framework\Command\ElasticsearchResetCommand;
use Shopwell\Elasticsearch\Framework\Command\ElasticsearchStatusCommand;
use Shopwell\Elasticsearch\Framework\Command\ElasticsearchTestAnalyzerCommand;
use Shopwell\Elasticsearch\Framework\Command\ElasticsearchUpdateMappingCommand;
use Shopwell\Elasticsearch\Framework\DataAbstractionLayer\AbstractElasticsearchAggregationHydrator;
use Shopwell\Elasticsearch\Framework\DataAbstractionLayer\AbstractElasticsearchSearchHydrator;
use Shopwell\Elasticsearch\Framework\DataAbstractionLayer\CriteriaParser;
use Shopwell\Elasticsearch\Framework\DataAbstractionLayer\ElasticsearchEntityAggregator;
use Shopwell\Elasticsearch\Framework\DataAbstractionLayer\ElasticsearchEntityAggregatorHydrator;
use Shopwell\Elasticsearch\Framework\DataAbstractionLayer\ElasticsearchEntitySearcher;
use Shopwell\Elasticsearch\Framework\DataAbstractionLayer\ElasticsearchEntitySearchHydrator;
use Shopwell\Elasticsearch\Framework\ElasticsearchFieldBuilder;
use Shopwell\Elasticsearch\Framework\ElasticsearchFieldMapper;
use Shopwell\Elasticsearch\Framework\ElasticsearchHelper;
use Shopwell\Elasticsearch\Framework\ElasticsearchIndexingUtils;
use Shopwell\Elasticsearch\Framework\ElasticsearchLanguageProvider;
use Shopwell\Elasticsearch\Framework\ElasticsearchOutdatedIndexDetector;
use Shopwell\Elasticsearch\Framework\ElasticsearchRegistry;
use Shopwell\Elasticsearch\Framework\ElasticsearchStagingHandler;
use Shopwell\Elasticsearch\Framework\Indexing\CreateAliasTask;
use Shopwell\Elasticsearch\Framework\Indexing\CreateAliasTaskHandler;
use Shopwell\Elasticsearch\Framework\Indexing\ElasticsearchIndexer;
use Shopwell\Elasticsearch\Framework\Indexing\IndexCreator;
use Shopwell\Elasticsearch\Framework\Indexing\IndexManager;
use Shopwell\Elasticsearch\Framework\Indexing\IndexMappingProvider;
use Shopwell\Elasticsearch\Framework\Indexing\IndexMappingUpdater;
use Shopwell\Elasticsearch\Framework\Subscriber\InvalidateExpiredCacheSubscriber;
use Shopwell\Elasticsearch\Framework\SystemUpdateListener;
use Shopwell\Elasticsearch\Product\AbstractProductSearchQueryBuilder;
use Shopwell\Elasticsearch\Product\CustomFieldSetGateway;
use Shopwell\Elasticsearch\Product\CustomFieldUpdater;
use Shopwell\Elasticsearch\Product\ElasticsearchCustomFieldsMappingHelper;
use Shopwell\Elasticsearch\Product\ElasticsearchProductDefinition;
use Shopwell\Elasticsearch\Product\LanguageSubscriber;
use Shopwell\Elasticsearch\Product\ProductCriteriaParser;
use Shopwell\Elasticsearch\Product\ProductCustomFieldsUsedUpdater;
use Shopwell\Elasticsearch\Product\ProductSearchBuilder;
use Shopwell\Elasticsearch\Product\ProductSearchQueryBuilder;
use Shopwell\Elasticsearch\Product\ProductUpdater;
use Shopwell\Elasticsearch\Product\SearchKeywordReplacement;
use Shopwell\Elasticsearch\Product\StopwordTokenFilter;
use Shopwell\Elasticsearch\Profiler\DataCollector;
use Shopwell\Elasticsearch\TokenQueryBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('elasticsearch.index.config', ['settings' => ['index' => '%elasticsearch.index_settings%', 'analysis' => '%elasticsearch.analysis%']]);
    $parameters->set('elasticsearch.index.mapping', ['dynamic_templates' => '%elasticsearch.dynamic_templates%']);
    $parameters->set('elasticsearch.administration.index.config', ['settings' => ['index' => '%elasticsearch.administration.index_settings%', 'analysis' => '%elasticsearch.administration.analysis%']]);
    $parameters->set('elasticsearch.administration.index.mapping', ['dynamic_templates' => '%elasticsearch.administration.dynamic_templates%']);

    $services->set(CriteriaParser::class)
        ->args([
            service(EntityDefinitionQueryHelper::class),
            service(CustomFieldService::class),
        ]);

    $services->set(ElasticsearchHelper::class)
        ->public()
        ->args([
            '%kernel.environment%',
            '%elasticsearch.enabled%',
            '%elasticsearch.indexing_enabled%',
            '%elasticsearch.index_prefix%',
            '%elasticsearch.throw_exception%',
            service(Client::class),
            service(ElasticsearchRegistry::class),
            service(CriteriaParser::class),
            service('shopwell.elasticsearch.logger'),
        ]);

    $services->set(ElasticsearchIndexingUtils::class)
        ->args([
            service(Connection::class),
            service('event_dispatcher'),
            service('parameter_bag'),
        ]);

    $services->set(ElasticsearchFieldBuilder::class)
        ->args([
            service(LanguageLoader::class),
            service(ElasticsearchIndexingUtils::class),
            '%elasticsearch.language_analyzer_mapping%',
        ]);

    $services->set(ElasticsearchFieldMapper::class)
        ->args([service(ElasticsearchIndexingUtils::class)]);

    $services->set(Client::class)
        ->public()
        ->lazy()
        ->args([
            '%elasticsearch.hosts%',
            service('shopwell.elasticsearch.logger'),
            '%kernel.debug%',
            '%elasticsearch.ssl%',
        ])
        ->factory([ClientFactory::class, 'createClient']);

    $services->set('admin.openSearch.client', Client::class)
        ->public()
        ->lazy()
        ->args([
            '%elasticsearch.administration.hosts%',
            service('shopwell.elasticsearch.logger'),
            '%kernel.debug%',
            '%elasticsearch.ssl%',
        ])
        ->factory([ClientFactory::class, 'createClient']);

    $services->set(IndexCreator::class)
        ->args([
            service(Client::class),
            '%elasticsearch.index.config%',
            service(IndexMappingProvider::class),
            service('event_dispatcher'),
            service(ElasticsearchHelper::class),
        ]);

    $services->set(IndexManager::class)
        ->args([
            service(Client::class),
            service(ElasticsearchHelper::class),
            service(ElasticsearchRegistry::class),
        ]);

    $services->set(InvalidateExpiredCacheSubscriber::class)
        ->args([service(IndexManager::class)])
        ->tag('kernel.event_subscriber');

    $services->set(IndexMappingProvider::class)
        ->args(['%elasticsearch.index.mapping%']);

    $services->set(IndexMappingUpdater::class)
        ->args([
            service(ElasticsearchRegistry::class),
            service(ElasticsearchHelper::class),
            service(Client::class),
            service(IndexMappingProvider::class),
            service(AbstractKeyValueStorage::class),
        ]);

    $services->set(ElasticsearchIndexingCommand::class)
        ->args([
            service(ElasticsearchIndexer::class),
            service('messenger.default_bus'),
            service(CreateAliasTaskHandler::class),
            '%elasticsearch.indexing_enabled%',
        ])
        ->tag('console.command');

    $services->set(ElasticsearchTestAnalyzerCommand::class)
        ->args([service(Client::class)])
        ->tag('console.command');

    $services->set(ElasticsearchStatusCommand::class)
        ->args([
            service(Client::class),
            service(Connection::class),
        ])
        ->tag('console.command');

    $services->set(ElasticsearchResetCommand::class)
        ->args([
            service(Client::class),
            service(ElasticsearchOutdatedIndexDetector::class),
            service(Connection::class),
            service('shopwell.increment.gateway.registry'),
        ])
        ->tag('console.command');

    $services->set(ElasticsearchUpdateMappingCommand::class)
        ->args([service(IndexMappingUpdater::class)])
        ->tag('console.command');

    $services->set(ElasticsearchLanguageProvider::class)
        ->args([
            service('language.repository'),
            service('event_dispatcher'),
        ]);

    $services->set(ProductUpdater::class)
        ->args([
            service(ElasticsearchIndexer::class),
            service(ProductDefinition::class),
        ])
        ->tag('kernel.event_subscriber');

    $services->set(AbstractElasticsearchSearchHydrator::class, ElasticsearchEntitySearchHydrator::class);

    $services->set(AbstractElasticsearchAggregationHydrator::class, ElasticsearchEntityAggregatorHydrator::class)
        ->args([service(DefinitionInstanceRegistry::class)]);

    $services->set(ElasticsearchEntitySearcher::class)
        ->public()
        ->decorate(EntitySearcherInterface::class, null, 1000)
        ->args([
            service(Client::class),
            service('Shopwell\Elasticsearch\Framework\DataAbstractionLayer\ElasticsearchEntitySearcher.inner'),
            service(ElasticsearchHelper::class),
            service(CriteriaParser::class),
            service(AbstractElasticsearchSearchHydrator::class),
            service('event_dispatcher'),
            '%elasticsearch.search.timeout%',
            '%elasticsearch.search.search_type%',
        ]);

    $services->set(ElasticsearchEntityAggregator::class)
        ->public()
        ->decorate(EntityAggregatorInterface::class, null, 1000)
        ->args([
            service(ElasticsearchHelper::class),
            service(Client::class),
            service('Shopwell\Elasticsearch\Framework\DataAbstractionLayer\ElasticsearchEntityAggregator.inner'),
            service(AbstractElasticsearchAggregationHydrator::class),
            service('event_dispatcher'),
            '%elasticsearch.search.timeout%',
            '%elasticsearch.search.search_type%',
        ]);

    $services->set(SearchKeywordReplacement::class)
        ->decorate(SearchKeywordUpdater::class, null, -50000)
        ->args([
            service('Shopwell\Elasticsearch\Product\SearchKeywordReplacement.inner'),
            service(ElasticsearchHelper::class),
        ]);

    $services->set(ProductSearchBuilder::class)
        ->decorate(ProductSearchBuilderInterface::class, null, -50000)
        ->args([
            service('Shopwell\Elasticsearch\Product\ProductSearchBuilder.inner'),
            service(ElasticsearchHelper::class),
            service(ProductDefinition::class),
            '%elasticsearch.search.term_max_length%',
        ]);

    $services->set(CreateAliasTaskHandler::class)
        ->public()
        ->args([
            service('scheduled_task.repository'),
            service('logger'),
            service(Client::class),
            service(Connection::class),
            service(ElasticsearchHelper::class),
            '%elasticsearch.index.config%',
            service('event_dispatcher'),
        ])
        ->tag('messenger.message_handler');

    $services->set(CreateAliasTask::class)
        ->tag('shopwell.scheduled.task');

    $services->set(ElasticsearchRegistry::class)
        ->args([tagged_iterator('shopwell.es.definition')]);

    $services->set(ElasticsearchStagingHandler::class)
        ->args([
            '%shopwell.staging.elasticsearch.check_for_existence%',
            service(ElasticsearchHelper::class),
            service(ElasticsearchOutdatedIndexDetector::class),
        ]);

    $services->set(ElasticsearchProductDefinition::class)
        ->args([
            service(ProductDefinition::class),
            service(Connection::class),
            service(AbstractProductSearchQueryBuilder::class),
            service(ElasticsearchFieldBuilder::class),
            service(ElasticsearchFieldMapper::class),
            service(SalesChannelLanguageLoader::class),
            '%elasticsearch.product.exclude_source%',
            '%kernel.environment%',
            service(LanguageLoader::class),
        ])
        ->tag('shopwell.es.definition');

    $services->set(StopwordTokenFilter::class)
        ->args([service(Connection::class)]);

    $services->set(SearchConfigLoader::class)
        ->args([service(Connection::class)]);

    $services->set(AbstractProductSearchQueryBuilder::class, ProductSearchQueryBuilder::class)
        ->args([
            service(ProductDefinition::class),
            service(StopwordTokenFilter::class),
            service(Tokenizer::class),
            service(SearchConfigLoader::class),
            service(TokenQueryBuilder::class),
        ]);

    $services->set(TokenQueryBuilder::class)
        ->args([
            service(DefinitionInstanceRegistry::class),
            service(CustomFieldService::class),
            '%elasticsearch.analysis.filter.sw_ngram_filter.min_gram%',
            '%elasticsearch.use_language_analyzer%',
        ]);

    $services->set(CustomFieldUpdater::class)
        ->args([
            service(ElasticsearchHelper::class),
            service(CustomFieldSetGateway::class),
            service(ElasticsearchCustomFieldsMappingHelper::class),
        ])
        ->tag('kernel.event_subscriber');

    $services->set(CustomFieldSetGateway::class)
        ->args([service(Connection::class)]);

    $services->set(ElasticsearchCustomFieldsMappingHelper::class)
        ->args([
            service(ElasticsearchOutdatedIndexDetector::class),
            service(Client::class),
            service(CustomFieldSetGateway::class),
        ]);

    $services->set(ProductCustomFieldsUsedUpdater::class)
        ->args([
            service(ElasticsearchHelper::class),
            service(ElasticsearchCustomFieldsMappingHelper::class),
            service(Connection::class),
        ])
        ->tag('kernel.event_subscriber');

    $services->set(ElasticsearchCreateAliasCommand::class)
        ->args([service(CreateAliasTaskHandler::class)])
        ->tag('console.command');

    $services->set(ElasticsearchCleanIndicesCommand::class)
        ->args([
            service(Client::class),
            service(ElasticsearchOutdatedIndexDetector::class),
        ])
        ->tag('console.command');

    $services->set(ElasticsearchAdminIndexingCommand::class)
        ->args([service(AdminSearchRegistry::class)])
        ->tag('console.command')
        ->tag('kernel.event_subscriber');

    $services->set(ElasticsearchAdminResetCommand::class)
        ->args([
            service('admin.openSearch.client'),
            service(Connection::class),
            service('shopwell.increment.gateway.registry'),
            service(AdminElasticsearchHelper::class),
        ])
        ->tag('console.command');

    $services->set(ElasticsearchAdminTestCommand::class)
        ->args([service(AdminSearcher::class)])
        ->tag('console.command');

    $services->set(ElasticsearchAdminUpdateMappingCommand::class)
        ->args([service(AdminSearchRegistry::class)])
        ->tag('console.command');

    $services->set(ElasticsearchOutdatedIndexDetector::class)
        ->args([
            service(Client::class),
            service(ElasticsearchRegistry::class),
            service(ElasticsearchHelper::class),
        ]);

    $services->set(ElasticsearchIndexer::class)
        ->args([
            service(Connection::class),
            service(ElasticsearchHelper::class),
            service(ElasticsearchRegistry::class),
            service(IndexCreator::class),
            service(IteratorFactory::class),
            service(Client::class),
            service('shopwell.elasticsearch.logger'),
            service('event_dispatcher'),
            '%elasticsearch.indexing_batch_size%',
        ])
        ->tag('messenger.message_handler');

    $services->set(LanguageSubscriber::class)
        ->args([
            service(ElasticsearchHelper::class),
            service(ElasticsearchRegistry::class),
            service(Client::class),
        ])
        ->tag('kernel.event_subscriber');

    $services->set(DataCollector::class)
        ->args([
            '%elasticsearch.enabled%',
            '%elasticsearch.administration.enabled%',
            service(Client::class),
            service('admin.openSearch.client'),
        ])
        ->tag('data_collector', ['template' => '@Elasticsearch/Collector/elasticsearch.html.twig', 'id' => 'elasticsearch']);

    $services->alias('shopwell.elasticsearch.logger', 'monolog.logger.elasticsearch');

    $services->set('_dummy_es_env_usage', ArrayIterator::class)
        ->public()
        ->lazy()
        ->args([['%env(bool:SHOPWELL_ES_ENABLED)%', '%env(bool:SHOPWELL_ES_INDEXING_ENABLED)%', '%env(string:OPENSEARCH_URL)%', '%env(string:SHOPWELL_ES_INDEX_PREFIX)%', '%env(bool:SHOPWELL_ES_THROW_EXCEPTION)%', '%env(int:SHOPWELL_ES_INDEXING_BATCH_SIZE)%']]);

    $services->set(RefreshIndexSubscriber::class)
        ->args([service(AdminSearchRegistry::class)])
        ->tag('kernel.event_subscriber');

    $services->set(SystemUpdateListener::class)
        ->args([
            service(AbstractKeyValueStorage::class),
            service(ElasticsearchIndexer::class),
            service('messenger.default_bus'),
            service(IndexMappingUpdater::class),
        ])
        ->tag('kernel.event_listener');

    $services->set(AdminElasticsearchHelper::class)
        ->public()
        ->args([
            '%elasticsearch.administration.enabled%',
            '%elasticsearch.administration.refresh_indices%',
            '%elasticsearch.administration.index_prefix%',
            '%kernel.environment%',
            '%elasticsearch.administration.throw_exception%',
            service('shopwell.elasticsearch.logger'),
        ]);

    $services->set(AdminSearchController::class)
        ->public()
        ->args([
            service(AdminSearcher::class),
            service(DefinitionInstanceRegistry::class),
            service(JsonEntityEncoder::class),
            service(AdminElasticsearchHelper::class),
        ]);

    $services->set(AdminSearcher::class)
        ->args([
            service('admin.openSearch.client'),
            service(AdminSearchRegistry::class),
            service(AdminElasticsearchHelper::class),
            service(DefinitionInstanceRegistry::class),
            service(AbstractElasticsearchSearchHydrator::class),
            service(ElasticsearchHelper::class),
            '%elasticsearch.administration.search.timeout%',
            '%elasticsearch.administration.search.term_max_length%',
            '%elasticsearch.administration.search.search_type%',
        ]);

    $services->set(AdminSearchRegistry::class)
        ->args([
            tagged_iterator('shopwell.elastic.admin-searcher-index', indexAttribute: 'key'),
            service(Connection::class),
            service('messenger.default_bus'),
            service('event_dispatcher'),
            service('admin.openSearch.client'),
            service(AdminElasticsearchHelper::class),
            service('shopwell.elasticsearch.logger'),
            '%elasticsearch.administration.index.config%',
            '%elasticsearch.administration.index.mapping%',
            '%kernel.environment%',
        ])
        ->tag('kernel.event_subscriber')
        ->tag('messenger.message_handler');

    $services->set(CustomerAdminSearchIndexer::class)
        ->args([
            service(Connection::class),
            service(IteratorFactory::class),
            service('customer.repository'),
            '%elasticsearch.administration.indexing_batch_size%',
        ])
        ->tag('shopwell.elastic.admin-searcher-index', ['key' => 'customer']);

    $services->set(CustomerGroupAdminSearchIndexer::class)
        ->args([
            service(Connection::class),
            service(IteratorFactory::class),
            service('customer_group.repository'),
            '%elasticsearch.administration.indexing_batch_size%',
        ])
        ->tag('shopwell.elastic.admin-searcher-index', ['key' => 'customer_group']);

    $services->set(LandingPageAdminSearchIndexer::class)
        ->args([
            service(Connection::class),
            service(IteratorFactory::class),
            service('landing_page.repository'),
            service(ElasticsearchFieldBuilder::class),
            '%elasticsearch.administration.indexing_batch_size%',
        ])
        ->tag('shopwell.elastic.admin-searcher-index', ['key' => 'landing_page']);

    $services->set(ManufacturerAdminSearchIndexer::class)
        ->args([
            service(Connection::class),
            service(IteratorFactory::class),
            service('product_manufacturer.repository'),
            service(ElasticsearchFieldBuilder::class),
            '%elasticsearch.administration.indexing_batch_size%',
        ])
        ->tag('shopwell.elastic.admin-searcher-index', ['key' => 'product_manufacturer']);

    $services->set(MediaAdminSearchIndexer::class)
        ->args([
            service(Connection::class),
            service(IteratorFactory::class),
            service('media.repository'),
            service(ElasticsearchFieldBuilder::class),
            '%elasticsearch.administration.indexing_batch_size%',
        ])
        ->tag('shopwell.elastic.admin-searcher-index', ['key' => 'media']);

    $services->set(OrderAdminSearchIndexer::class)
        ->args([
            service(Connection::class),
            service(IteratorFactory::class),
            service('order.repository'),
            '%elasticsearch.administration.indexing_batch_size%',
        ])
        ->tag('shopwell.elastic.admin-searcher-index', ['key' => 'order']);

    $services->set(PaymentMethodAdminSearchIndexer::class)
        ->args([
            service(Connection::class),
            service(IteratorFactory::class),
            service('payment_method.repository'),
            '%elasticsearch.administration.indexing_batch_size%',
        ])
        ->tag('shopwell.elastic.admin-searcher-index', ['key' => 'payment_method']);

    $services->set(ProductAdminSearchIndexer::class)
        ->args([
            service(Connection::class),
            service(IteratorFactory::class),
            service('product.repository'),
            service(ElasticsearchFieldBuilder::class),
            '%elasticsearch.administration.indexing_batch_size%',
        ])
        ->tag('shopwell.elastic.admin-searcher-index', ['key' => 'product']);

    $services->set(PromotionAdminSearchIndexer::class)
        ->args([
            service(Connection::class),
            service(IteratorFactory::class),
            service('promotion.repository'),
            service(ElasticsearchFieldBuilder::class),
            '%elasticsearch.administration.indexing_batch_size%',
        ])
        ->tag('shopwell.elastic.admin-searcher-index', ['key' => 'promotion']);

    $services->set(PropertyGroupAdminSearchIndexer::class)
        ->args([
            service(Connection::class),
            service(IteratorFactory::class),
            service('property_group.repository'),
            service(ElasticsearchFieldBuilder::class),
            '%elasticsearch.administration.indexing_batch_size%',
        ])
        ->tag('shopwell.elastic.admin-searcher-index', ['key' => 'property_group']);

    $services->set(SalesChannelAdminSearchIndexer::class)
        ->args([
            service(Connection::class),
            service(IteratorFactory::class),
            service('sales_channel.repository'),
            '%elasticsearch.administration.indexing_batch_size%',
        ])
        ->tag('shopwell.elastic.admin-searcher-index', ['key' => 'sales_channel']);

    $services->set(ShippingMethodAdminSearchIndexer::class)
        ->args([
            service(Connection::class),
            service(IteratorFactory::class),
            service('shipping_method.repository'),
            '%elasticsearch.administration.indexing_batch_size%',
        ])
        ->tag('shopwell.elastic.admin-searcher-index', ['key' => 'shipping_method']);

    $services->set(CategoryAdminSearchIndexer::class)
        ->args([
            service(Connection::class),
            service(IteratorFactory::class),
            service('category.repository'),
            service(ElasticsearchFieldBuilder::class),
            '%elasticsearch.administration.indexing_batch_size%',
        ])
        ->tag('shopwell.elastic.admin-searcher-index', ['key' => 'category']);

    $services->set(ProductStreamAdminSearchIndexer::class)
        ->args([
            service(Connection::class),
            service(IteratorFactory::class),
            service('product_stream.repository'),
            '%elasticsearch.administration.indexing_batch_size%',
        ])
        ->tag('shopwell.elastic.admin-searcher-index', ['key' => 'product_stream']);

    $services->set(ProductCriteriaParser::class)
        ->args([
            service(EntityDefinitionQueryHelper::class),
            service(CustomFieldService::class),
        ]);

    $services->set(AdminElasticsearchEntitySearcher::class)
        ->public()
        ->decorate(EntitySearcherInterface::class, null, 500)
        ->args([
            service('.inner'),
            service(AdminSearchRegistry::class),
            service(AdminElasticsearchHelper::class),
            service(AdminSearcher::class),
        ]);
};
