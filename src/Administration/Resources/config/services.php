<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\DBAL\Connection;
use Shopwell\Administration\Command\DeleteAdminFilesAfterBuildCommand;
use Shopwell\Administration\Command\DeleteExtensionLocalPublicFilesCommand;
use Shopwell\Administration\Controller\AdminExtensionApiController;
use Shopwell\Administration\Controller\AdministrationController;
use Shopwell\Administration\Controller\AdminProductStreamController;
use Shopwell\Administration\Controller\AdminSearchController;
use Shopwell\Administration\Controller\AdminTagController;
use Shopwell\Administration\Controller\DashboardController;
use Shopwell\Administration\Controller\NotificationController;
use Shopwell\Administration\Controller\UserConfigController;
use Shopwell\Administration\Dashboard\OrderAmountService;
use Shopwell\Administration\Framework\Adapter\Cache\Http\AdministrationCacheControlListener;
use Shopwell\Administration\Framework\App\Subscriber\SystemLanguageChangedSubscriber;
use Shopwell\Administration\Framework\Routing\AdministrationRouteScope;
use Shopwell\Administration\Framework\Routing\KnownIps\KnownIpsCollector;
use Shopwell\Administration\Framework\Routing\NotFound\AdministrationNotFoundSubscriber;
use Shopwell\Administration\Framework\SystemCheck\AdministrationReadinessCheck;
use Shopwell\Administration\Framework\Twig\ViteFileAccessorDecorator;
use Shopwell\Administration\Service\AdminSearcher;
use Shopwell\Administration\Snippet\AppAdministrationSnippetDefinition;
use Shopwell\Administration\Snippet\AppAdministrationSnippetPersister;
use Shopwell\Administration\Snippet\AppLifecycleSubscriber;
use Shopwell\Administration\Snippet\CachedSnippetFinder;
use Shopwell\Administration\Snippet\SnippetFinder;
use Shopwell\Administration\System\SalesChannel\Subscriber\SalesChannelUserConfigSubscriber;
use Shopwell\Core\Checkout\Cart\Price\CashRounding;
use Shopwell\Core\Content\Product\ProductDefinition;
use Shopwell\Core\Framework\Adapter\Cache\CacheInvalidator;
use Shopwell\Core\Framework\Adapter\Cache\Http\Event\BeforeCacheControlEvent;
use Shopwell\Core\Framework\Adapter\Twig\TemplateFinder;
use Shopwell\Core\Framework\Api\Acl\AclCriteriaValidator;
use Shopwell\Core\Framework\Api\OAuth\SymfonyBearerTokenValidator;
use Shopwell\Core\Framework\Api\Serializer\JsonEntityEncoder;
use Shopwell\Core\Framework\App\ActionButton\Executor;
use Shopwell\Core\Framework\App\Hmac\QuerySigner;
use Shopwell\Core\Framework\App\Payload\AppPayloadServiceHelper;
use Shopwell\Core\Framework\App\Source\SourceResolver;
use Shopwell\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\RequestCriteriaBuilder;
use Shopwell\Core\Framework\Notification\NotificationService;
use Shopwell\Core\Framework\Store\Services\FirstRunWizardService;
use Shopwell\Core\Framework\Util\HtmlSanitizer;
use Shopwell\Core\System\SalesChannel\Context\SalesChannelContextService;
use Shopwell\Core\System\Snippet\Service\TranslationLoader;
use Shopwell\Core\System\Snippet\Struct\TranslationConfig;
use Shopwell\Core\System\SystemConfig\SystemConfigService;
use Shopwell\Core\System\Tag\Service\FilterTagIdsService;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouterInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('env(SHOPWELL_ADMINISTRATION_PATH_NAME)', 'admin');
    $parameters->set('shopwell_administration.path_name', '%env(resolve:SHOPWELL_ADMINISTRATION_PATH_NAME)%');

    $services->set(DeleteAdminFilesAfterBuildCommand::class)
        ->args([service(Filesystem::class)])
        ->tag('console.command');

    $services->set(DeleteExtensionLocalPublicFilesCommand::class)
        ->args([service('kernel')])
        ->tag('console.command');

    $services->set(AdminExtensionApiController::class)
        ->public()
        ->args([
            service(Executor::class),
            service(AppPayloadServiceHelper::class),
            service('app.repository'),
            service(QuerySigner::class),
        ])
        ->call('setContainer', [service('service_container')]);

    $services->set(AdministrationController::class)
        ->public()
        ->args([
            service(TemplateFinder::class),
            service(FirstRunWizardService::class),
            service(SnippetFinder::class),
            '%kernel.supported_api_versions%',
            service(KnownIpsCollector::class),
            service(Connection::class),
            service('event_dispatcher'),
            '%kernel.shopwell_core_dir%',
            service('customer.repository'),
            service('currency.repository'),
            service(HtmlSanitizer::class),
            service(DefinitionInstanceRegistry::class),
            service('parameter_bag'),
            service(SystemConfigService::class),
            service('shopwell.filesystem.asset'),
            '%env(SERVICE_REGISTRY_URL)%',
            service('language.repository'),
            service(SymfonyBearerTokenValidator::class),
            '%env(PRODUCT_ANALYTICS_GATEWAY_URL)%',
            '%shopwell.api.refresh_token_ttl%',
        ])
        ->call('setContainer', [service('service_container')]);

    $services->set(AdminSearchController::class)
        ->public()
        ->args([
            service(RequestCriteriaBuilder::class),
            service(DefinitionInstanceRegistry::class),
            service(AdminSearcher::class),
            service('serializer'),
            service(AclCriteriaValidator::class),
            service(DefinitionInstanceRegistry::class),
            service(JsonEntityEncoder::class),
        ])
        ->call('setContainer', [service('service_container')]);

    $services->set(UserConfigController::class)
        ->public()
        ->args([
            service('user_config.repository'),
            service(Connection::class),
        ])
        ->call('setContainer', [service('service_container')]);

    $services->set(AdminProductStreamController::class)
        ->public()
        ->args([
            service(ProductDefinition::class),
            service('sales_channel.product.repository'),
            service(SalesChannelContextService::class),
            service(RequestCriteriaBuilder::class),
        ])
        ->call('setContainer', [service('service_container')]);

    $services->set(AdminTagController::class)
        ->public()
        ->args([service(FilterTagIdsService::class)])
        ->call('setContainer', [service('service_container')]);

    $services->set(NotificationController::class)
        ->public()
        ->args([
            service('shopwell.rate_limiter'),
            service(NotificationService::class),
        ])
        ->call('setContainer', [service('service_container')]);

    $services->set(AdminSearcher::class)
        ->args([service(DefinitionInstanceRegistry::class)]);

    $services->set(AdministrationNotFoundSubscriber::class)
        ->args([
            '%shopwell_administration.path_name%',
            service('service_container'),
        ])
        ->tag('kernel.event_subscriber');

    $services->set(AdministrationRouteScope::class)
        ->args(['%shopwell_administration.path_name%'])
        ->tag('shopwell.route_scope');

    $services->set(AdministrationReadinessCheck::class)
        ->args([
            service(RouterInterface::class),
            service(KernelInterface::class),
            service(ViteFileAccessorDecorator::class),
            service('filesystem'),
        ])
        ->tag('shopwell.system_check');

    $services->set(AppAdministrationSnippetDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(AppAdministrationSnippetPersister::class)
        ->args([
            service('app_administration_snippet.repository'),
            service('locale.repository'),
            service(CacheInvalidator::class),
            service(Filesystem::class),
        ]);

    $services->set(SnippetFinder::class)
        ->args([
            service('kernel'),
            service(Connection::class),
            service('shopwell.filesystem.private'),
            service(TranslationConfig::class),
            service(TranslationLoader::class),
            service(HtmlSanitizer::class),
        ]);

    $services->set(CachedSnippetFinder::class)
        ->decorate(SnippetFinder::class)
        ->args([
            service('Shopwell\Administration\Snippet\CachedSnippetFinder.inner'),
            service('cache.object'),
        ]);

    $services->set(AppLifecycleSubscriber::class)
        ->args([
            service(SourceResolver::class),
            service(AppAdministrationSnippetPersister::class),
        ])
        ->tag('kernel.event_subscriber');

    $services->set(KnownIpsCollector::class);

    $services->set(SalesChannelUserConfigSubscriber::class)
        ->args([service('user_config.repository')])
        ->tag('kernel.event_subscriber');

    $services->set(OrderAmountService::class)
        ->args([
            service(Connection::class),
            service(CashRounding::class),
        ]);

    $services->set(DashboardController::class)
        ->public()
        ->args([service(OrderAmountService::class)])
        ->call('setContainer', [service('service_container')]);

    $services->set(ViteFileAccessorDecorator::class)
        ->decorate('pentatrion_vite.file_accessor')
        ->args([
            '%pentatrion_vite.configs%',
            service('shopwell.asset.asset'),
            service('kernel'),
            service('filesystem'),
        ]);

    $services->set(SystemLanguageChangedSubscriber::class)
        ->args([
            service('locale.repository'),
            service('app_administration_snippet.repository'),
        ])
        ->tag('kernel.event_subscriber');

    $services->set(AdministrationCacheControlListener::class)
        ->tag('kernel.event_listener', ['event' => BeforeCacheControlEvent::class]);
};
