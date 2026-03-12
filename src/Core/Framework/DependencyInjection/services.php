<?php declare(strict_types=1);

use Cocur\Slugify\Bridge\Twig\SlugifyExtension;
use Cocur\Slugify\Slugify;
use Doctrine\DBAL\Connection;
use Shopwell\Core\Checkout\Cart\ApiOrderCartService;
use Shopwell\Core\Checkout\Cart\SalesChannel\CartOrderRoute;
use Shopwell\Core\Checkout\Cart\SalesChannel\CartService;
use Shopwell\Core\Checkout\Customer\ImitateCustomerTokenGenerator;
use Shopwell\Core\Framework\Adapter\Cache\CacheClearer;
use Shopwell\Core\Framework\Adapter\Cache\CacheTagCollector;
use Shopwell\Core\Framework\Adapter\Cache\Http\CacheStore;
use Shopwell\Core\Framework\Adapter\Cache\Http\HttpCacheKeyGenerator;
use Shopwell\Core\Framework\Adapter\Cache\RedisConnectionFactory;
use Shopwell\Core\Framework\Adapter\Command\S3FilesystemVisibilityCommand;
use Shopwell\Core\Framework\Adapter\Kernel\EnvIntOrNullProcessor;
use Shopwell\Core\Framework\Adapter\Kernel\HttpCacheKernel;
use Shopwell\Core\Framework\Adapter\Kernel\HttpKernel;
use Shopwell\Core\Framework\Adapter\Redis\RedisConnectionProvider;
use Shopwell\Core\Framework\Adapter\Storage\AbstractKeyValueStorage;
use Shopwell\Core\Framework\Adapter\Storage\MySQLKeyValueStorage;
use Shopwell\Core\Framework\Adapter\Translation\Translator;
use Shopwell\Core\Framework\Adapter\Twig\AppTemplateIterator;
use Shopwell\Core\Framework\Adapter\Twig\EntityTemplateLoader;
use Shopwell\Core\Framework\Adapter\Twig\Extension\ComparisonExtension;
use Shopwell\Core\Framework\Adapter\Twig\Extension\FeatureFlagExtension;
use Shopwell\Core\Framework\Adapter\Twig\Extension\InAppPurchaseExtension;
use Shopwell\Core\Framework\Adapter\Twig\Extension\InstanceOfExtension;
use Shopwell\Core\Framework\Adapter\Twig\Extension\NodeExtension;
use Shopwell\Core\Framework\Adapter\Twig\Extension\PcreExtension;
use Shopwell\Core\Framework\Adapter\Twig\Extension\PhpSyntaxExtension;
use Shopwell\Core\Framework\Adapter\Twig\Filter\CurrencyFilter;
use Shopwell\Core\Framework\Adapter\Twig\Filter\EmailIdnTwigFilter;
use Shopwell\Core\Framework\Adapter\Twig\Filter\LeadingSpacesFilter;
use Shopwell\Core\Framework\Adapter\Twig\Filter\ReplaceRecursiveFilter;
use Shopwell\Core\Framework\Adapter\Twig\NamespaceHierarchy\BundleHierarchyBuilder;
use Shopwell\Core\Framework\Adapter\Twig\NamespaceHierarchy\NamespaceHierarchyBuilder;
use Shopwell\Core\Framework\Adapter\Twig\SecurityExtension;
use Shopwell\Core\Framework\Adapter\Twig\StringTemplateRenderer;
use Shopwell\Core\Framework\Adapter\Twig\SwTwigFunctionResetter;
use Shopwell\Core\Framework\Adapter\Twig\TemplateFinder;
use Shopwell\Core\Framework\Adapter\Twig\TemplateIterator;
use Shopwell\Core\Framework\Adapter\Twig\TemplateScopeDetector;
use Shopwell\Core\Framework\Adapter\Twig\TwigVariableParserFactory;
use Shopwell\Core\Framework\Api\Controller\SalesChannelProxyController;
use Shopwell\Core\Framework\App\ActiveAppsLoader;
use Shopwell\Core\Framework\App\Source\SourceResolver;
use Shopwell\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\RequestCriteriaBuilder;
use Shopwell\Core\Framework\Extensions\ExtensionDispatcher;
use Shopwell\Core\Framework\Feature\Command\FeatureDisableCommand;
use Shopwell\Core\Framework\Feature\Command\FeatureDumpCommand;
use Shopwell\Core\Framework\Feature\Command\FeatureEnableCommand;
use Shopwell\Core\Framework\Feature\Command\FeatureListCommand;
use Shopwell\Core\Framework\Feature\FeatureFlagRegistry;
use Shopwell\Core\Framework\Log\ExceptionLogger;
use Shopwell\Core\Framework\Log\LogEntryDefinition;
use Shopwell\Core\Framework\Log\LoggingService;
use Shopwell\Core\Framework\Log\Monolog\DoctrineSQLHandler;
use Shopwell\Core\Framework\Log\Monolog\ErrorCodeLogLevelHandler;
use Shopwell\Core\Framework\Log\Monolog\ExcludeExceptionHandler;
use Shopwell\Core\Framework\Log\Monolog\ExcludeFlowEventHandler;
use Shopwell\Core\Framework\Log\ScheduledTask\LogCleanupTask;
use Shopwell\Core\Framework\Log\ScheduledTask\LogCleanupTaskHandler;
use Shopwell\Core\Framework\Migration\Command\CreateMigrationCommand;
use Shopwell\Core\Framework\Migration\Command\MigrationCommand;
use Shopwell\Core\Framework\Migration\Command\MigrationDestructiveCommand;
use Shopwell\Core\Framework\Migration\Command\RefreshMigrationCommand;
use Shopwell\Core\Framework\Migration\IndexerQueuer;
use Shopwell\Core\Framework\Migration\MigrationCollectionLoader;
use Shopwell\Core\Framework\Migration\MigrationInfo;
use Shopwell\Core\Framework\Migration\MigrationRuntime;
use Shopwell\Core\Framework\Migration\MigrationSource;
use Shopwell\Core\Framework\Plugin\KernelPluginCollection;
use Shopwell\Core\Framework\Routing\Annotation\CriteriaValueResolver;
use Shopwell\Core\Framework\Routing\ApiRequestContextResolver;
use Shopwell\Core\Framework\Routing\ApiRouteScope;
use Shopwell\Core\Framework\Routing\CanonicalRedirectService;
use Shopwell\Core\Framework\Routing\ContextResolverListener;
use Shopwell\Core\Framework\Routing\CoreSubscriber;
use Shopwell\Core\Framework\Routing\Facade\RequestFacadeFactory;
use Shopwell\Core\Framework\Routing\MaintenanceModeResolver;
use Shopwell\Core\Framework\Routing\PaymentScopeWhitelist;
use Shopwell\Core\Framework\Routing\QueryDataBagResolver;
use Shopwell\Core\Framework\Routing\RequestDataBagResolver;
use Shopwell\Core\Framework\Routing\RequestTransformerInterface;
use Shopwell\Core\Framework\Routing\RouteEventSubscriber;
use Shopwell\Core\Framework\Routing\RouteParamsCleanupListener;
use Shopwell\Core\Framework\Routing\RouteScope;
use Shopwell\Core\Framework\Routing\RouteScopeListener;
use Shopwell\Core\Framework\Routing\RouteScopeRegistry;
use Shopwell\Core\Framework\Routing\SalesChannelRequestContextResolver;
use Shopwell\Core\Framework\Routing\StoreApiRouteScope;
use Shopwell\Core\Framework\Routing\SymfonyRouteScopeWhitelist;
use Shopwell\Core\Framework\Routing\Validation\Constraint\RouteNotBlockedValidator;
use Shopwell\Core\Framework\Routing\Validation\RouteBlocklistService;
use Shopwell\Core\Framework\Script\Execution\ScriptExecutor;
use Shopwell\Core\Framework\Store\InAppPurchase;
use Shopwell\Core\Framework\Struct\Serializer\StructNormalizer;
use Shopwell\Core\Framework\Util\Backtrace\BacktraceCollector;
use Shopwell\Core\Framework\Util\HtmlSanitizer;
use Shopwell\Core\Framework\Validation\DataValidator;
use Shopwell\Core\Kernel;
use Shopwell\Core\System\Currency\CurrencyFormatter;
use Shopwell\Core\System\CustomEntity\CustomEntityLifecycleService;
use Shopwell\Core\System\CustomEntity\Schema\CustomEntityPersister;
use Shopwell\Core\System\CustomEntity\Schema\CustomEntitySchemaUpdater;
use Shopwell\Core\System\CustomEntity\Xml\Config\AdminUi\AdminUiXmlSchemaValidator;
use Shopwell\Core\System\CustomEntity\Xml\Config\CustomEntityEnrichmentService;
use Shopwell\Core\System\CustomEntity\Xml\CustomEntityXmlSchemaValidator;
use Shopwell\Core\System\Locale\LanguageLocaleCodeProvider;
use Shopwell\Core\System\SalesChannel\Context\SalesChannelContextPersister;
use Shopwell\Core\System\SalesChannel\Context\SalesChannelContextService;
use Shopwell\Core\System\Snippet\Api\SnippetController;
use Shopwell\Core\System\Snippet\Files\AppSnippetFileLoader;
use Shopwell\Core\System\Snippet\Files\SnippetFileCollection;
use Shopwell\Core\System\Snippet\Files\SnippetFileCollectionFactory;
use Shopwell\Core\System\Snippet\Files\SnippetFileLoader;
use Shopwell\Core\System\Snippet\Filter\AddedFilter;
use Shopwell\Core\System\Snippet\Filter\AuthorFilter;
use Shopwell\Core\System\Snippet\Filter\EditedFilter;
use Shopwell\Core\System\Snippet\Filter\EmptySnippetFilter;
use Shopwell\Core\System\Snippet\Filter\NamespaceFilter;
use Shopwell\Core\System\Snippet\Filter\SnippetFilterFactory;
use Shopwell\Core\System\Snippet\Filter\TermFilter;
use Shopwell\Core\System\Snippet\Filter\TranslationKeyFilter;
use Shopwell\Core\System\Snippet\Service\TranslationLoader;
use Shopwell\Core\System\Snippet\SnippetService;
use Shopwell\Core\System\Snippet\Struct\TranslationConfig;
use Shopwell\Core\System\SystemConfig\SystemConfigService;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Runtime\Runner\Symfony\HttpKernelRunner;
use Symfony\Component\Runtime\Runner\Symfony\ResponseRunner;
use Symfony\Component\Runtime\SymfonyRuntime;
use Twig\Extra\Intl\IntlExtension;
use Twig\Extra\String\StringExtension;

use function Symfony\Component\DependencyInjection\Loader\Configurator\env;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $parameters = $container->parameters();
    $parameters->set('shopwell.slug.config', [
        'regexp' => '/([^A-Za-z0-9\.]|-)+/',
        'lowercase' => false,
    ]);

    $parameters->set('shopwell.routing.registered_api_prefixes', []);

    $parameters->set('core.migration.directories', []);
    $parameters->set('shopwell.security.csp_templates', [
        'default' => "object-src 'none';\nscript-src 'none';\nbase-uri 'self';\nframe-ancestors 'none';",
        'administration' => "object-src 'none';\nscript-src 'strict-dynamic' 'nonce-%%nonce%%' 'unsafe-inline' 'unsafe-eval' https: http:;\nbase-uri 'self';\nframe-ancestors 'none';",
        'storefront' => '',
        'installer' => '',
    ]);

    $parameters->set('shopwell_http_cache_enabled_default', 1);
    $parameters->set('shopwell.http.cache.enabled', env('default:shopwell_http_cache_enabled_default:SHOPWELL_HTTP_CACHE_ENABLED'));

    $container->extension('monolog', [
        'channels' => [
            'business_events',
        ],
        'handlers' => [
            'business_event_handler_buffer' => [
                'type' => 'buffer',
                'handler' => 'business_event_handler',
                'channels' => ['business_events'],
            ],
            'business_event_handler' => [
                'type' => 'service',
                'id' => DoctrineSQLHandler::class,
                'channels' => ['business_events'],
            ],
        ],
    ]);

    $services = $container->services();

    $services->set(Connection::class)
        ->public()
        ->factory([Kernel::class, 'getConnection']);

    $services->set(QueryDataBagResolver::class)
        ->tag('controller.argument_value_resolver', ['priority' => 1000]);

    $services->set(RequestDataBagResolver::class)
        ->tag('controller.argument_value_resolver', ['priority' => 1000]);

    $services->set('slugify', Slugify::class)
        ->private()
        ->args(['%shopwell.slug.config%']);

    $services->set('Shopwell\Core\Framework\Migration\MigrationSource.core', MigrationSource::class)
        ->args(['core'])
        ->tag('shopwell.migration_source');

    $services->set('Shopwell\Core\Framework\Migration\MigrationSource.core.V6_3', MigrationSource::class)
        ->args(['core.V6_3'])
        ->tag('shopwell.migration_source');

    $services->set('Shopwell\Core\Framework\Migration\MigrationSource.core.V6_4', MigrationSource::class)
        ->args(['core.V6_4'])
        ->tag('shopwell.migration_source');

    $services->set('Shopwell\Core\Framework\Migration\MigrationSource.core.V6_5', MigrationSource::class)
        ->args(['core.V6_5'])
        ->tag('shopwell.migration_source');

    $services->set('Shopwell\Core\Framework\Migration\MigrationSource.core.V6_6', MigrationSource::class)
        ->args(['core.V6_6'])
        ->tag('shopwell.migration_source');

    $services->set('Shopwell\Core\Framework\Migration\MigrationSource.core.V6_7', MigrationSource::class)
        ->args(['core.V6_7'])
        ->tag('shopwell.migration_source');

    $services->set('Shopwell\Core\Framework\Migration\MigrationSource.core.V6_8', MigrationSource::class)
        ->args(['core.V6_8'])
        ->tag('shopwell.migration_source');

    $services->set('Shopwell\Core\Framework\Migration\MigrationSource.null', MigrationSource::class)
        ->args([
            'null',
            [],
        ])
        ->tag('shopwell.migration_source');

    $services->set(MigrationRuntime::class)
        ->args([
            service(Connection::class),
            service('logger'),
        ]);

    $services->set(MigrationCollectionLoader::class)
        ->public()
        ->args([
            service(Connection::class),
            service(MigrationRuntime::class),
            service('logger'),
            tagged_iterator('shopwell.migration_source'),
        ]);

    $services->set(MigrationInfo::class)
        ->args([service(Connection::class)]);

    $services->set(CreateMigrationCommand::class)
        ->args([
            service(KernelPluginCollection::class),
            '%kernel.shopwell_core_dir%',
            '%kernel.shopwell_version%',
        ])
        ->tag('console.command');

    $services->set(RefreshMigrationCommand::class)
        ->tag('console.command');

    $services->set(MigrationCommand::class)
        ->args([
            service(MigrationCollectionLoader::class),
            service('cache.object'),
            '%kernel.shopwell_version%',
        ])
        ->tag('console.command');

    $services->set(MigrationDestructiveCommand::class)
        ->args([
            service(MigrationCollectionLoader::class),
            service('cache.object'),
            '%kernel.shopwell_version%',
        ])
        ->tag('console.command');

    $services->set(IndexerQueuer::class)
        ->public()
        ->args([service(Connection::class)]);

    $services->set(StructNormalizer::class)
        ->tag('serializer.normalizer');

    $services->set(ContextResolverListener::class)
        ->args([service(ApiRequestContextResolver::class)])
        ->tag('kernel.event_subscriber');

    $services->set(CoreSubscriber::class)
        ->args([
            '%shopwell.security.csp_templates%',
            service(ScriptExecutor::class),
        ])
        ->tag('kernel.event_subscriber');

    $services->set(SymfonyRouteScopeWhitelist::class)
        ->tag('shopwell.route_scope_whitelist');

    $services->set(PaymentScopeWhitelist::class)
        ->tag('shopwell.route_scope_whitelist');

    $services->set(RouteScopeListener::class)
        ->args([
            service(RouteScopeRegistry::class),
            service('request_stack'),
            tagged_iterator('shopwell.route_scope_whitelist'),
        ])
        ->tag('kernel.event_subscriber');

    $services->set(CanonicalRedirectService::class)
        ->public()
        ->args([
            service(SystemConfigService::class),
            service(ExtensionDispatcher::class),
        ]);

    $services->set(RouteEventSubscriber::class)
        ->args([service('event_dispatcher')])
        ->tag('kernel.event_subscriber');

    $services->set(MaintenanceModeResolver::class)
        ->args([service('event_dispatcher')]);

    $services->set(RouteBlocklistService::class)
        ->args([service('router')]);

    $services->set(RouteNotBlockedValidator::class)
        ->args([service(RouteBlocklistService::class)])
        ->tag('validator.constraint_validator');

    $services->set(CustomEntityEnrichmentService::class)
        ->args([service(AdminUiXmlSchemaValidator::class)]);

    $services->set(CustomEntityLifecycleService::class)
        ->args([
            service(CustomEntityPersister::class),
            service(CustomEntitySchemaUpdater::class),
            service(CustomEntityEnrichmentService::class),
            service(CustomEntityXmlSchemaValidator::class),
            service(SourceResolver::class),
        ]);

    $services->set(Translator::class)
        ->decorate('translator')
        ->args([
            service('Shopwell\Core\Framework\Adapter\Translation\Translator.inner'),
            service('request_stack'),
            service('cache.object'),
            service('translator.formatter'),
            '%kernel.environment%',
            service(Connection::class),
            service(LanguageLocaleCodeProvider::class),
            service(SnippetService::class),
            service(CacheTagCollector::class),
        ])
        ->tag('monolog.logger');

    $services->set(SnippetService::class)
        ->lazy()
        ->args([
            service(Connection::class),
            service(SnippetFileCollection::class),
            service('snippet.repository'),
            service('snippet_set.repository'),
            service(SnippetFilterFactory::class),
            service(ExtensionDispatcher::class),
            service('event_dispatcher'),
            service('shopwell.filesystem.private'),
            service('filesystem'),
        ]);

    $services->set(SnippetController::class)
        ->public()
        ->args([
            service(SnippetService::class),
            service(SnippetFileCollection::class),
        ])
        ->call('setContainer', [service('service_container')]);

    $services->set(SnippetFileLoader::class)
        ->args([
            service(KernelInterface::class),
            service(Connection::class),
            service(AppSnippetFileLoader::class),
            service(ActiveAppsLoader::class),
            service(TranslationConfig::class),
            service(TranslationLoader::class),
            service('shopwell.filesystem.private'),
        ]);

    $services->set(AppSnippetFileLoader::class)
        ->args(['%kernel.project_dir%']);

    $services->set(SnippetFileCollection::class)
        ->public()
        ->lazy()
        ->factory([service(SnippetFileCollectionFactory::class), 'createSnippetFileCollection']);

    $services->set(SnippetFileCollectionFactory::class)
        ->args([service(SnippetFileLoader::class)]);

    $services->set(SnippetFilterFactory::class)
        ->public()
        ->args([tagged_iterator('shopwell.snippet.filter')]);

    $services->set(AuthorFilter::class)
        ->tag('shopwell.snippet.filter');

    $services->set(AddedFilter::class)
        ->tag('shopwell.snippet.filter');

    $services->set(EditedFilter::class)
        ->tag('shopwell.snippet.filter');

    $services->set(EmptySnippetFilter::class)
        ->tag('shopwell.snippet.filter');

    $services->set(NamespaceFilter::class)
        ->tag('shopwell.snippet.filter');

    $services->set(TermFilter::class)
        ->tag('shopwell.snippet.filter');

    $services->set(TranslationKeyFilter::class)
        ->tag('shopwell.snippet.filter');

    $services->set(TemplateFinder::class)
        ->public()
        ->args([
            service('twig'),
            service('twig.loader'),
            '%twig.cache%',
            service(NamespaceHierarchyBuilder::class),
            service(TemplateScopeDetector::class),
        ])
        ->tag('kernel.reset', ['method' => 'reset']);

    $services->set(SwTwigFunctionResetter::class)
        ->public()
        ->tag('kernel.reset', ['method' => 'reset']);

    $services->set(NamespaceHierarchyBuilder::class)
        ->args([tagged_iterator('shopwell.twig.hierarchy_builder')]);

    $services->set(BundleHierarchyBuilder::class)
        ->args([
            service('kernel'),
            service(Connection::class),
        ])
        ->tag('shopwell.twig.hierarchy_builder', ['priority' => 1000]);

    $services->set(TemplateScopeDetector::class)
        ->args([service('request_stack')]);

    $services->set(NodeExtension::class)
        ->args([
            service(TemplateFinder::class),
            service(TemplateScopeDetector::class),
        ])
        ->tag('twig.extension');

    $services->set(PhpSyntaxExtension::class)
        ->tag('twig.extension')
        ->tag('shopwell.seo_url.twig.extension')
        ->tag('shopwell.app_script.twig.extension');

    $services->set(FeatureFlagExtension::class)
        ->tag('twig.extension');

    $services->set('twig.extension.intl', IntlExtension::class)
        ->tag('twig.extension');

    $services->set('twig.extension.string', StringExtension::class)
        ->tag('twig.extension');

    $services->set('twig.extension.trans', TranslationExtension::class)
        ->args([service('translator')])
        ->tag('twig.extension')
        ->tag('shopwell.app_script.twig.extension');

    $services->set(PcreExtension::class)
        ->tag('twig.extension')
        ->tag('shopwell.app_script.twig.extension');

    $services->set(InstanceOfExtension::class)
        ->tag('twig.extension');

    $services->set(CurrencyFilter::class)
        ->args([service(CurrencyFormatter::class)])
        ->tag('twig.extension');

    $services->set(EmailIdnTwigFilter::class)
        ->tag('twig.extension');

    $services->set(LeadingSpacesFilter::class)
        ->tag('twig.extension');

    $services->set(SlugifyExtension::class)
        ->args([service('slugify')])
        ->tag('twig.extension')
        ->tag('shopwell.seo_url.twig.extension');

    $services->set(ReplaceRecursiveFilter::class)
        ->tag('twig.extension')
        ->tag('shopwell.app_script.twig.extension');

    $services->set(ComparisonExtension::class)
        ->tag('shopwell.app_script.twig.extension');

    $services->set(SecurityExtension::class)
        ->args(['%shopwell.twig.allowed_php_functions%'])
        ->tag('twig.extension')
        ->tag('shopwell.seo_url.twig.extension')
        ->tag('shopwell.app_script.twig.extension');

    $services->set(InAppPurchaseExtension::class)
        ->args([service(InAppPurchase::class)])
        ->tag('twig.extension');

    $services->set(StringTemplateRenderer::class)
        ->args([
            service('twig'),
            '%shopwell.cache.twig.string_template_renderer_cache_dir%',
        ]);

    $services->set(TemplateIterator::class)
        ->public()
        ->decorate('twig.template_iterator')
        ->args([
            service('Shopwell\Core\Framework\Adapter\Twig\TemplateIterator.inner'),
            '%kernel.bundles%',
        ]);

    $services->set(EntityTemplateLoader::class)
        ->args([
            service(Connection::class),
            '%kernel.environment%',
        ])
        ->tag('twig.loader')
        ->tag('kernel.event_subscriber')
        ->tag('kernel.reset', ['method' => 'reset']);

    $services->set(AppTemplateIterator::class)
        ->public()
        ->decorate('twig.template_iterator')
        ->args([
            service('Shopwell\Core\Framework\Adapter\Twig\AppTemplateIterator.inner'),
            service('app_template.repository'),
        ]);

    $services->set(TwigVariableParserFactory::class);

    $services->set(ApiRequestContextResolver::class)
        ->args([
            service(Connection::class),
            service(RouteScopeRegistry::class),
        ]);

    $services->set(SalesChannelRequestContextResolver::class)
        ->decorate(ApiRequestContextResolver::class)
        ->args([
            service('Shopwell\Core\Framework\Routing\SalesChannelRequestContextResolver.inner'),
            service(SalesChannelContextService::class),
            service('event_dispatcher'),
            service(RouteScopeRegistry::class),
        ]);

    $services->set(ApiOrderCartService::class)
        ->args([
            service(CartService::class),
            service(SalesChannelContextPersister::class),
        ]);

    $services->set(SalesChannelProxyController::class)
        ->public()
        ->args([
            service('kernel'),
            service('sales_channel.repository'),
            service(DataValidator::class),
            service(SalesChannelContextPersister::class),
            service(SalesChannelContextService::class),
            service('event_dispatcher'),
            service(ApiOrderCartService::class),
            service(CartOrderRoute::class),
            service(CartService::class),
            service('request_stack'),
            service(ImitateCustomerTokenGenerator::class),
        ])
        ->call('setContainer', [service('service_container')]);

    $services->set(RouteScope::class)
        ->tag('shopwell.route_scope');

    $services->set(ApiRouteScope::class)
        ->tag('shopwell.route_scope');

    $services->set(StoreApiRouteScope::class)
        ->tag('shopwell.route_scope');

    $services->set(RouteScopeRegistry::class)
        ->args([tagged_iterator('shopwell.route_scope')]);

    $services->set(LoggingService::class)
        ->args([
            '%kernel.environment%',
            service('monolog.logger.business_events'),
        ])
        ->tag('kernel.event_subscriber');

    $services->set(ExceptionLogger::class)
        ->args([
            '%kernel.environment%',
            '%shopwell.logger.enforce_throw_exception%',
            service('logger'),
        ]);

    $services->set(LogCleanupTask::class)
        ->tag('shopwell.scheduled.task');

    $services->set(LogCleanupTaskHandler::class)
        ->args([
            service('scheduled_task.repository'),
            service('logger'),
            service(SystemConfigService::class),
            service(Connection::class),
        ])
        ->tag('messenger.message_handler');

    $services->set(DoctrineSQLHandler::class)
        ->args([service(Connection::class)]);

    $services->set(LogEntryDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(CriteriaValueResolver::class)
        ->args([
            service(DefinitionInstanceRegistry::class),
            service(RequestCriteriaBuilder::class),
        ])
        ->tag('controller.argument_value_resolver');

    $services->set(FeatureDumpCommand::class)
        ->args([service('kernel')])
        ->tag('console.command')
        ->tag('console.command', ['command' => 'administration:dump:features']);

    $services->set(FeatureDisableCommand::class)
        ->args([
            service(FeatureFlagRegistry::class),
            service(CacheClearer::class),
        ])
        ->tag('console.command');

    $services->set(FeatureEnableCommand::class)
        ->args([
            service(FeatureFlagRegistry::class),
            service(CacheClearer::class),
        ])
        ->tag('console.command');

    $services->set(FeatureListCommand::class)
        ->tag('console.command');

    $services->set(S3FilesystemVisibilityCommand::class)
        ->args([
            service('shopwell.filesystem.private'),
            service('shopwell.filesystem.public'),
            service('shopwell.filesystem.theme'),
            service('shopwell.filesystem.sitemap'),
            service('shopwell.filesystem.asset'),
        ])
        ->tag('console.command');

    $services->set(HtmlSanitizer::class)
        ->public()
        ->args([
            '%shopwell.html_sanitizer.cache_dir%',
            '%shopwell.html_sanitizer.cache_enabled%',
            '%shopwell.html_sanitizer.sets%',
            '%shopwell.html_sanitizer.fields%',
            '%shopwell.html_sanitizer.enabled%',
        ])
        ->tag('kernel.reset', ['method' => 'reset']);

    $services->set(ExcludeExceptionHandler::class)
        ->decorate('monolog.handler.main', null, 0, ContainerInterface::IGNORE_ON_INVALID_REFERENCE)
        ->args([
            service('Shopwell\Core\Framework\Log\Monolog\ExcludeExceptionHandler.inner'),
            '%shopwell.logger.exclude_exception%',
        ]);

    $services->set(ErrorCodeLogLevelHandler::class)
        ->decorate('monolog.handler.main', null, 0, ContainerInterface::IGNORE_ON_INVALID_REFERENCE)
        ->args([
            service('Shopwell\Core\Framework\Log\Monolog\ErrorCodeLogLevelHandler.inner'),
            '%shopwell.logger.error_code_log_levels%',
        ]);

    $services->set(ExcludeFlowEventHandler::class)
        ->decorate('monolog.handler.main', null, 0, ContainerInterface::IGNORE_ON_INVALID_REFERENCE)
        ->args([
            service('Shopwell\Core\Framework\Log\Monolog\ExcludeFlowEventHandler.inner'),
            '%shopwell.logger.exclude_events%',
        ]);

    $services->set(RouteParamsCleanupListener::class)
        ->tag('kernel.event_listener');

    $services->set(RedisConnectionFactory::class)
        ->args(['%shopwell.cache.redis_prefix%']);

    $services->set(RedisConnectionProvider::class)
        ->args(['']);

    $services->set(RequestFacadeFactory::class)
        ->public()
        ->args([service('request_stack')]);

    $services->set(AbstractKeyValueStorage::class, MySQLKeyValueStorage::class)
        ->public()
        ->args([service(Connection::class)]);

    $services->set('http_kernel', HttpKernel::class)
        ->public()
        ->args([
            service('event_dispatcher'),
            service('controller_resolver'),
            service('request_stack'),
            service('argument_resolver'),
            service(RequestTransformerInterface::class),
            service(CanonicalRedirectService::class),
        ])
        ->tag('container.hot_path')
        ->tag('container.preload', ['class' => HttpKernelRunner::class])
        ->tag('container.preload', ['class' => ResponseRunner::class])
        ->tag('container.preload', ['class' => SymfonyRuntime::class]);

    $services->set('http_kernel.cache', HttpCacheKernel::class)
        ->decorate('http_kernel')
        ->args([
            service('.inner'),
            service(CacheStore::class),
            service('esi'),
            [],
            service('event_dispatcher'),
            '%shopwell.http_cache.reverse_proxy.enabled%',
        ]);

    $services->set(CacheStore::class)
        ->public()
        ->args([
            service('cache.http'),
            service('event_dispatcher'),
            service(HttpCacheKeyGenerator::class),
            service(MaintenanceModeResolver::class),
            '%session.storage.options%',
            service(CacheTagCollector::class),
            '%shopwell.http_cache.soft_purge%',
            service('messenger.bus.default'),
        ]);

    $services->set(HttpCacheKeyGenerator::class)
        ->args([
            '%kernel.cache.hash%',
            service('event_dispatcher'),
            '%shopwell.http_cache.ignored_url_parameters%',
        ]);

    $services->set(BacktraceCollector::class);

    $services->set(EnvIntOrNullProcessor::class)
        ->tag('container.env_var_processor');
};
