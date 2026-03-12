<?php declare(strict_types=1);

use Doctrine\DBAL\Connection;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Shopwell\Core\Checkout\Cart\SalesChannel\CartService;
use Shopwell\Core\Content\Category\Event\CategoryIndexerEvent;
use Shopwell\Core\Content\LandingPage\Event\LandingPageIndexerEvent;
use Shopwell\Core\Content\Media\Event\MediaIndexerEvent;
use Shopwell\Core\Content\Product\Events\InvalidateProductCache;
use Shopwell\Core\Content\Rule\Event\RuleIndexerEvent;
use Shopwell\Core\Content\Sitemap\Event\SitemapGeneratedEvent;
use Shopwell\Core\Framework\Adapter\Cache\CacheClearer;
use Shopwell\Core\Framework\Adapter\Cache\CacheInvalidationSubscriber;
use Shopwell\Core\Framework\Adapter\Cache\CacheInvalidator;
use Shopwell\Core\Framework\Adapter\Cache\CacheTagCollection;
use Shopwell\Core\Framework\Adapter\Cache\CacheTagCollector;
use Shopwell\Core\Framework\Adapter\Cache\Http\CacheHeadersService;
use Shopwell\Core\Framework\Adapter\Cache\Http\CachePolicyProvider;
use Shopwell\Core\Framework\Adapter\Cache\Http\CachePolicyProviderFactory;
use Shopwell\Core\Framework\Adapter\Cache\Http\CacheRelevantRulesResolver;
use Shopwell\Core\Framework\Adapter\Cache\Http\CacheResponseSubscriber;
use Shopwell\Core\Framework\Adapter\Cache\Http\CacheStore;
use Shopwell\Core\Framework\Adapter\Cache\InvalidateCacheTask;
use Shopwell\Core\Framework\Adapter\Cache\InvalidateCacheTaskHandler;
use Shopwell\Core\Framework\Adapter\Cache\InvalidatorStorage\AbstractInvalidatorStorage;
use Shopwell\Core\Framework\Adapter\Cache\InvalidatorStorage\MySQLInvalidatorStorage;
use Shopwell\Core\Framework\Adapter\Cache\InvalidatorStorage\RedisInvalidatorStorage;
use Shopwell\Core\Framework\Adapter\Cache\Message\CleanupOldCacheFoldersHandler;
use Shopwell\Core\Framework\Adapter\Cache\Message\RefreshHttpCacheMessageHandler;
use Shopwell\Core\Framework\Adapter\Cache\ReverseProxy\AbstractReverseProxyGateway;
use Shopwell\Core\Framework\Adapter\Cache\ReverseProxy\FastlyReverseProxyGateway;
use Shopwell\Core\Framework\Adapter\Cache\ReverseProxy\ReverseProxyCache;
use Shopwell\Core\Framework\Adapter\Cache\ReverseProxy\VarnishReverseProxyGateway;
use Shopwell\Core\Framework\Adapter\Cache\Script\Facade\CacheInvalidatorFacadeHookFactory;
use Shopwell\Core\Framework\Adapter\Cache\Script\ScriptCacheInvalidationSubscriber;
use Shopwell\Core\Framework\Adapter\Cache\StampedeProtectionConfigurator;
use Shopwell\Core\Framework\Adapter\Cache\Telemetry\CacheTelemetrySubscriber;
use Shopwell\Core\Framework\Adapter\Command\CacheClearAllCommand;
use Shopwell\Core\Framework\Adapter\Command\CacheClearHttpCommand;
use Shopwell\Core\Framework\Adapter\Command\CacheInvalidateDelayedCommand;
use Shopwell\Core\Framework\Adapter\Kernel\EsiDecoration;
use Shopwell\Core\Framework\Adapter\Redis\RedisConnectionProvider;
use Shopwell\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopwell\Core\Framework\DependencyInjection\TaggedServiceLocator;
use Shopwell\Core\Framework\Extensions\ExtensionDispatcher;
use Shopwell\Core\Framework\Plugin\Event\PluginPostActivateEvent;
use Shopwell\Core\Framework\Plugin\Event\PluginPostDeactivateEvent;
use Shopwell\Core\Framework\Plugin\Event\PluginPostInstallEvent;
use Shopwell\Core\Framework\Plugin\Event\PluginPostUpdateEvent;
use Shopwell\Core\Framework\Routing\MaintenanceModeResolver;
use Shopwell\Core\Framework\Script\Execution\ScriptExecutor;
use Shopwell\Core\Framework\Telemetry\Metrics\Meter;
use Shopwell\Core\Framework\Util\Backtrace\BacktraceCollector;
use Shopwell\Core\System\SystemConfig\Event\SystemConfigChangedHook;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_locator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(StampedeProtectionConfigurator::class)
        ->public()
        ->args(['%shopwell.cache.disable_stampede_protection%']);

    $services->set('shopwell.cache.invalidator.storage.redis_adapter', 'Redis')
        ->public()
        ->args(['%shopwell.cache.invalidation.delay_options.connection%'])
        ->factory([service(RedisConnectionProvider::class), 'getConnection']);

    $services->set('shopwell.cache.invalidator.storage.redis', RedisInvalidatorStorage::class)
        ->lazy()
        ->args([
            service('shopwell.cache.invalidator.storage.redis_adapter'),
            service('logger'),
        ])
        ->tag('shopwell.cache.invalidator.storage', ['storage' => 'redis']);

    $services->set('shopwell.cache.invalidator.storage.mysql', MySQLInvalidatorStorage::class)
        ->lazy()
        ->args([
            service(Connection::class),
            service('logger'),
        ])
        ->tag('shopwell.cache.invalidator.storage', ['storage' => 'mysql']);

    $services->set('shopwell.cache.invalidator.storage.locator', TaggedServiceLocator::class)
        ->args([tagged_locator('shopwell.cache.invalidator.storage', indexAttribute: 'storage')]);

    $services->set(AbstractInvalidatorStorage::class)
        ->args(['%shopwell.cache.invalidation.delay_options.storage%'])
        ->factory([service('shopwell.cache.invalidator.storage.locator'), 'get']);

    $services->set(CacheInvalidator::class)
        ->public()
        ->lazy()
        ->args([
            [service('cache.object'), service('cache.http')],
            service(AbstractInvalidatorStorage::class),
            service('event_dispatcher'),
            service(LoggerInterface::class),
            service('request_stack'),
            service('cache.http'),
            '%shopwell.http_cache.soft_purge%',
            '%shopwell.cache.invalidation.delay_enabled%',
            '%shopwell.cache.invalidation.tag_invalidation_log_enabled%',
            service(BacktraceCollector::class),
            service(AbstractReverseProxyGateway::class)->nullOnInvalid(),
        ]);

    $services->set(InvalidateCacheTask::class)
        ->tag('shopwell.scheduled.task');

    $services->set(InvalidateCacheTaskHandler::class)
        ->args([
            service('scheduled_task.repository'),
            service('logger'),
            service(CacheInvalidator::class),
        ])
        ->tag('messenger.message_handler');

    $services->set(CacheClearer::class)
        ->args([
            ['object' => service('cache.object'), 'http' => service('cache.http')],
            service('cache_clearer'),
            service(AbstractReverseProxyGateway::class)->nullOnInvalid(),
            service(CacheInvalidator::class),
            service('filesystem'),
            '%kernel.cache_dir%',
            '%kernel.environment%',
            '%shopwell.deployment.cluster_setup%',
            '%shopwell.http_cache.reverse_proxy.enabled%',
            service('messenger.default_bus'),
            service('logger'),
            service('lock.factory'),
        ]);

    $services->set(CleanupOldCacheFoldersHandler::class)
        ->args([service(CacheClearer::class)])
        ->tag('messenger.message_handler');

    $services->set(RefreshHttpCacheMessageHandler::class)
        ->args([
            service('http_kernel.cache.inner'),
            service(CacheStore::class),
            service('cache.http'),
        ])
        ->tag('messenger.message_handler');

    $services->set(CacheInvalidatorFacadeHookFactory::class)
        ->public()
        ->args([service(CacheInvalidator::class)]);

    $services->set(ScriptCacheInvalidationSubscriber::class)
        ->args([service(ScriptExecutor::class)])
        ->tag('kernel.event_subscriber');

    $services->set(CacheInvalidationSubscriber::class)
        ->args([
            service(CacheInvalidator::class),
            service(Connection::class),
            '%shopwell.product_stream.indexing%',
        ])
        ->tag('kernel.event_listener', ['event' => CategoryIndexerEvent::class, 'method' => 'invalidateCategoryRouteByCategoryIds', 'priority' => 2000])
        ->tag('kernel.event_listener', ['event' => LandingPageIndexerEvent::class, 'method' => 'invalidateIndexedLandingPages', 'priority' => 2000])
        ->tag('kernel.event_listener', ['event' => InvalidateProductCache::class, 'method' => 'invalidateProduct', 'priority' => 2001])
        ->tag('kernel.event_listener', ['event' => EntityWrittenContainerEvent::class, 'method' => 'invalidateCurrencyRoute', 'priority' => 2002])
        ->tag('kernel.event_listener', ['event' => EntityWrittenContainerEvent::class, 'method' => 'invalidateLanguageRoute', 'priority' => 2003])
        ->tag('kernel.event_listener', ['event' => EntityWrittenContainerEvent::class, 'method' => 'invalidateNavigationRoute', 'priority' => 2004])
        ->tag('kernel.event_listener', ['event' => EntityWrittenContainerEvent::class, 'method' => 'invalidatePaymentMethodRoute', 'priority' => 2005])
        ->tag('kernel.event_listener', ['event' => EntityWrittenContainerEvent::class, 'method' => 'invalidateManufacturerFilters', 'priority' => 2007])
        ->tag('kernel.event_listener', ['event' => EntityWrittenContainerEvent::class, 'method' => 'invalidatePropertyFilters', 'priority' => 2008])
        ->tag('kernel.event_listener', ['event' => EntityWrittenContainerEvent::class, 'method' => 'invalidateContext', 'priority' => 2010])
        ->tag('kernel.event_listener', ['event' => EntityWrittenContainerEvent::class, 'method' => 'invalidateShippingMethodRoute', 'priority' => 2011])
        ->tag('kernel.event_listener', ['event' => EntityWrittenContainerEvent::class, 'method' => 'invalidateSnippets', 'priority' => 2012])
        ->tag('kernel.event_listener', ['event' => EntityWrittenContainerEvent::class, 'method' => 'invalidateStreamsBeforeIndexing', 'priority' => 2013])
        ->tag('kernel.event_listener', ['event' => EntityWrittenContainerEvent::class, 'method' => 'invalidateStreamIds', 'priority' => 2014])
        ->tag('kernel.event_listener', ['event' => EntityWrittenContainerEvent::class, 'method' => 'invalidateCountryRoute', 'priority' => 2015])
        ->tag('kernel.event_listener', ['event' => EntityWrittenContainerEvent::class, 'method' => 'invalidateInitialStateIdLoader', 'priority' => 2017])
        ->tag('kernel.event_listener', ['event' => EntityWrittenContainerEvent::class, 'method' => 'invalidateCountryStateRoute', 'priority' => 2018])
        ->tag('kernel.event_listener', ['event' => RuleIndexerEvent::class, 'method' => 'invalidateRules', 'priority' => 2000])
        ->tag('kernel.event_listener', ['event' => PluginPostInstallEvent::class, 'method' => 'invalidateRules', 'priority' => 2000])
        ->tag('kernel.event_listener', ['event' => PluginPostInstallEvent::class, 'method' => 'invalidateConfig', 'priority' => 2001])
        ->tag('kernel.event_listener', ['event' => PluginPostActivateEvent::class, 'method' => 'invalidateRules', 'priority' => 2000])
        ->tag('kernel.event_listener', ['event' => PluginPostActivateEvent::class, 'method' => 'invalidateConfig', 'priority' => 2001])
        ->tag('kernel.event_listener', ['event' => PluginPostUpdateEvent::class, 'method' => 'invalidateRules', 'priority' => 2000])
        ->tag('kernel.event_listener', ['event' => PluginPostUpdateEvent::class, 'method' => 'invalidateConfig', 'priority' => 2001])
        ->tag('kernel.event_listener', ['event' => PluginPostDeactivateEvent::class, 'method' => 'invalidateRules', 'priority' => 2000])
        ->tag('kernel.event_listener', ['event' => PluginPostDeactivateEvent::class, 'method' => 'invalidateConfig', 'priority' => 2001])
        ->tag('kernel.event_listener', ['event' => SystemConfigChangedHook::class, 'method' => 'invalidateConfigKey', 'priority' => 2000])
        ->tag('kernel.event_listener', ['event' => SitemapGeneratedEvent::class, 'method' => 'invalidateSitemap', 'priority' => 2000])
        ->tag('kernel.event_listener', ['event' => MediaIndexerEvent::class, 'method' => 'invalidateMedia', 'priority' => 2000]);

    $services->set(CacheTagCollector::class)
        ->args([
            service('request_stack'),
            service('event_dispatcher'),
        ])
        ->tag('kernel.event_listener');

    $services->set(CacheTagCollection::class);

    $services->set(CachePolicyProvider::class)
        ->args([
            '%shopwell.http_cache.policies%',
            '%shopwell.http_cache.route_policies%',
            '%shopwell.http_cache.default_policies%',
        ])
        ->factory([CachePolicyProviderFactory::class, 'create']);

    $services->set(CacheResponseSubscriber::class)
        ->args([
            service(CartService::class),
            '%shopwell.http.cache.enabled%',
            service(MaintenanceModeResolver::class),
            service(CacheHeadersService::class),
            service(CachePolicyProvider::class),
        ])
        ->tag('kernel.event_subscriber');

    $services->set(CacheHeadersService::class)
        ->args([
            service(ExtensionDispatcher::class),
            service(CacheRelevantRulesResolver::class),
            '%shopwell.http_cache.cookies%',
            service('event_dispatcher'),
        ]);

    $services->set(CacheRelevantRulesResolver::class)
        ->args([service(ExtensionDispatcher::class)]);

    $services->set('esi', EsiDecoration::class);

    $services->set(ReverseProxyCache::class)
        ->args([
            service(AbstractReverseProxyGateway::class),
            '%shopwell.cache.invalidation.http_cache%',
            service(CacheTagCollector::class),
        ])
        ->tag('kernel.event_listener');

    $services->set(CacheInvalidateDelayedCommand::class)
        ->args([service(CacheInvalidator::class)])
        ->tag('console.command');

    $services->set(CacheClearAllCommand::class)
        ->args([
            service(CacheClearer::class),
            '%kernel.environment%',
            '%kernel.debug%',
        ])
        ->tag('console.command');

    $services->set(CacheClearHttpCommand::class)
        ->args([service(CacheClearer::class)])
        ->tag('console.command');

    $services->set('shopwell.reverse_proxy.http_client', Client::class);

    $services->set(AbstractReverseProxyGateway::class, VarnishReverseProxyGateway::class)
        ->args([
            '%shopwell.http_cache.reverse_proxy.hosts%',
            '%shopwell.http_cache.reverse_proxy.max_parallel_invalidations%',
            service('shopwell.reverse_proxy.http_client'),
            service('logger'),
        ]);

    $services->set(FastlyReverseProxyGateway::class)
        ->args([
            service('shopwell.reverse_proxy.http_client'),
            '%shopwell.http_cache.reverse_proxy.fastly.service_id%',
            '%shopwell.http_cache.reverse_proxy.fastly.api_key%',
            '%shopwell.http_cache.reverse_proxy.fastly.soft_purge%',
            '%shopwell.http_cache.reverse_proxy.max_parallel_invalidations%',
            '%shopwell.http_cache.reverse_proxy.fastly.tag_prefix%',
            '%shopwell.http_cache.reverse_proxy.fastly.instance_tag%',
            '%env(APP_URL)%',
            service('logger'),
        ]);

    $services->set(CacheTelemetrySubscriber::class)
        ->args([service(Meter::class)])
        ->tag('kernel.event_subscriber');
};
