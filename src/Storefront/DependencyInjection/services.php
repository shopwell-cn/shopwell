<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Checkout\Cart\CartCalculator;
use Shopwell\Core\Checkout\Cart\CartPersister;
use Shopwell\Core\Checkout\Cart\Order\OrderConverter;
use Shopwell\Core\Checkout\Cart\SalesChannel\CartService;
use Shopwell\Core\Checkout\Customer\SalesChannel\CustomerGroupRegistrationSettingsRoute;
use Shopwell\Core\Checkout\Customer\SalesChannel\CustomerRecoveryIsExpiredRoute;
use Shopwell\Core\Checkout\Customer\SalesChannel\CustomerRoute;
use Shopwell\Core\Checkout\Customer\SalesChannel\ListAddressRoute;
use Shopwell\Core\Checkout\Customer\SalesChannel\LoadWishlistRoute;
use Shopwell\Core\Checkout\Customer\Validation\AddressValidationFactory;
use Shopwell\Core\Checkout\Gateway\SalesChannel\CheckoutGatewayRoute;
use Shopwell\Core\Checkout\Order\SalesChannel\OrderRoute;
use Shopwell\Core\Checkout\Order\SalesChannel\OrderService;
use Shopwell\Core\Checkout\Payment\SalesChannel\PaymentMethodRoute;
use Shopwell\Core\Checkout\Shipping\SalesChannel\ShippingMethodRoute;
use Shopwell\Core\Content\Category\SalesChannel\CategoryRoute;
use Shopwell\Core\Content\Category\Service\NavigationLoader;
use Shopwell\Core\Content\LandingPage\SalesChannel\LandingPageRoute;
use Shopwell\Core\Content\Media\File\FileSaver;
use Shopwell\Core\Content\Media\MediaService;
use Shopwell\Core\Content\Product\SalesChannel\Detail\ProductConfiguratorLoader;
use Shopwell\Core\Content\Product\SalesChannel\Detail\ProductDetailRoute;
use Shopwell\Core\Content\Product\SalesChannel\ProductCloseoutFilterFactory;
use Shopwell\Core\Content\Product\SalesChannel\ProductListRoute;
use Shopwell\Core\Content\Product\SalesChannel\Search\ProductSearchRoute;
use Shopwell\Core\Content\Product\SalesChannel\Suggest\ProductSuggestRoute;
use Shopwell\Core\Content\Seo\HreflangLoaderInterface;
use Shopwell\Core\Content\Seo\SeoResolver;
use Shopwell\Core\Content\Seo\SeoUrlPersister;
use Shopwell\Core\Content\Seo\SeoUrlPlaceholderHandlerInterface;
use Shopwell\Core\Content\Sitemap\SalesChannel\SitemapRoute;
use Shopwell\Core\Framework\Adapter\Cache\CacheInvalidator;
use Shopwell\Core\Framework\Adapter\Cache\CacheTagCollector;
use Shopwell\Core\Framework\Adapter\Translation\Translator;
use Shopwell\Core\Framework\Adapter\Twig\TemplateFinder;
use Shopwell\Core\Framework\App\ActiveAppsLoader;
use Shopwell\Core\Framework\App\ShopId\ShopIdProvider;
use Shopwell\Core\Framework\App\Source\SourceResolver;
use Shopwell\Core\Framework\App\Template\TemplateLoader;
use Shopwell\Core\Framework\DataAbstractionLayer\Cache\EntityCacheKeyGenerator;
use Shopwell\Core\Framework\Event\BeforeSendResponseEvent;
use Shopwell\Core\Framework\Routing\RequestTransformerInterface;
use Shopwell\Core\Framework\Validation\DataValidator;
use Shopwell\Core\Maintenance\SalesChannel\Service\SalesChannelCreator;
use Shopwell\Core\System\Country\SalesChannel\CountryRoute;
use Shopwell\Core\System\Country\SalesChannel\CountryStateRoute;
use Shopwell\Core\System\Currency\SalesChannel\CurrencyRoute;
use Shopwell\Core\System\Language\SalesChannel\LanguageRoute;
use Shopwell\Core\System\SalesChannel\Context\SalesChannelContextService;
use Shopwell\Core\System\SalesChannel\SalesChannel\ContextSwitchRoute;
use Shopwell\Core\System\SystemConfig\SystemConfigService;
use Shopwell\Storefront\Checkout\Cart\SalesChannel\StorefrontCartFacade;
use Shopwell\Storefront\Checkout\Customer\CustomerGroupSubscriber;
use Shopwell\Storefront\Checkout\Payment\BlockedPaymentMethodSwitcher;
use Shopwell\Storefront\Checkout\Shipping\BlockedShippingMethodSwitcher;
use Shopwell\Storefront\Event\CartMergedSubscriber;
use Shopwell\Storefront\Framework\AffiliateTracking\AffiliateTrackingListener;
use Shopwell\Storefront\Framework\App\Template\IconTemplateLoader;
use Shopwell\Storefront\Framework\Cache\CacheCookieEventSubscriber;
use Shopwell\Storefront\Framework\Captcha\BasicCaptcha\BasicCaptchaGenerator;
use Shopwell\Storefront\Framework\Command\SalesChannelCreateStorefrontCommand;
use Shopwell\Storefront\Framework\Media\StorefrontMediaUploader;
use Shopwell\Storefront\Framework\Media\StorefrontMediaValidatorRegistry;
use Shopwell\Storefront\Framework\Media\Validator\StorefrontMediaDocumentValidator;
use Shopwell\Storefront\Framework\Media\Validator\StorefrontMediaImageValidator;
use Shopwell\Storefront\Framework\Routing\CachedDomainLoader;
use Shopwell\Storefront\Framework\Routing\CachedDomainLoaderInvalidator;
use Shopwell\Storefront\Framework\Routing\CanonicalLinkListener;
use Shopwell\Storefront\Framework\Routing\DomainLoader;
use Shopwell\Storefront\Framework\Routing\DomainNotMappedListener;
use Shopwell\Storefront\Framework\Routing\MaintenanceModeResolver;
use Shopwell\Storefront\Framework\Routing\NotFound\NotFoundSubscriber;
use Shopwell\Storefront\Framework\Routing\RequestTransformer;
use Shopwell\Storefront\Framework\Routing\ResponseHeaderListener;
use Shopwell\Storefront\Framework\Routing\RobotsRouteScopeWhitelist;
use Shopwell\Storefront\Framework\Routing\Router;
use Shopwell\Storefront\Framework\Routing\StorefrontRouteScope;
use Shopwell\Storefront\Framework\Routing\StorefrontSubscriber;
use Shopwell\Storefront\Framework\Routing\TemplateDataSubscriber;
use Shopwell\Storefront\Framework\SystemCheck\ProductDetailReadinessCheck;
use Shopwell\Storefront\Framework\SystemCheck\ProductListingReadinessCheck;
use Shopwell\Storefront\Framework\SystemCheck\SalesChannelsReadinessCheck;
use Shopwell\Storefront\Framework\SystemCheck\Util\SalesChannelDomainProvider;
use Shopwell\Storefront\Framework\SystemCheck\Util\SalesChannelDomainUtil;
use Shopwell\Storefront\Framework\Twig\ErrorTemplateResolver;
use Shopwell\Storefront\Framework\Twig\Extension\ConfigExtension;
use Shopwell\Storefront\Framework\Twig\Extension\IconCacheTwigFilter;
use Shopwell\Storefront\Framework\Twig\Extension\UrlEncodingTwigFilter;
use Shopwell\Storefront\Framework\Twig\IconExtension;
use Shopwell\Storefront\Framework\Twig\TemplateConfigAccessor;
use Shopwell\Storefront\Framework\Twig\TemplateDataExtension;
use Shopwell\Storefront\Framework\Twig\ThumbnailExtension;
use Shopwell\Storefront\Framework\Twig\TwigAppVariable;
use Shopwell\Storefront\Framework\Twig\TwigDateRequestListener;
use Shopwell\Storefront\Page\Account\CustomerGroupRegistration\CustomerGroupRegistrationPageLoader;
use Shopwell\Storefront\Page\Account\Login\AccountLoginPageLoader;
use Shopwell\Storefront\Page\Account\Order\AccountEditOrderPageLoader;
use Shopwell\Storefront\Page\Account\Order\AccountOrderPageLoader;
use Shopwell\Storefront\Page\Account\Overview\AccountOverviewPageLoader;
use Shopwell\Storefront\Page\Account\Profile\AccountProfilePageLoader;
use Shopwell\Storefront\Page\Account\RecoverPassword\AccountRecoverPasswordPageLoader;
use Shopwell\Storefront\Page\Address\Detail\AddressDetailPageLoader;
use Shopwell\Storefront\Page\Address\Listing\AddressListingPageLoader;
use Shopwell\Storefront\Page\Checkout\Cart\CheckoutCartPageLoader;
use Shopwell\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoader;
use Shopwell\Storefront\Page\Checkout\Finish\CheckoutFinishPageLoader;
use Shopwell\Storefront\Page\Checkout\Offcanvas\OffcanvasCartPageLoader;
use Shopwell\Storefront\Page\Checkout\Register\CheckoutRegisterPageLoader;
use Shopwell\Storefront\Page\GenericPageLoader;
use Shopwell\Storefront\Page\LandingPage\LandingPageLoader;
use Shopwell\Storefront\Page\Navigation\Error\ErrorPageLoader;
use Shopwell\Storefront\Page\Navigation\NavigationPageLoader;
use Shopwell\Storefront\Page\Product\Configurator\ProductPageConfiguratorLoader;
use Shopwell\Storefront\Page\Product\ProductPageLoader;
use Shopwell\Storefront\Page\Product\QuickView\MinimalQuickViewPageLoader;
use Shopwell\Storefront\Page\Robots\Parser\RobotsDirectiveParser;
use Shopwell\Storefront\Page\Robots\RobotsConfigChangeSubscriber;
use Shopwell\Storefront\Page\Robots\RobotsPageLoader;
use Shopwell\Storefront\Page\Search\SearchPageLoader;
use Shopwell\Storefront\Page\Sitemap\SitemapPageLoader;
use Shopwell\Storefront\Page\Suggest\SuggestPageLoader;
use Shopwell\Storefront\Page\Wishlist\GuestWishlistPageLoader;
use Shopwell\Storefront\Page\Wishlist\WishlistPageLoader;
use Shopwell\Storefront\Pagelet\Captcha\BasicCaptchaPageletLoader;
use Shopwell\Storefront\Pagelet\Country\CountryStateDataPageletLoader;
use Shopwell\Storefront\Pagelet\Footer\FooterPageletLoader;
use Shopwell\Storefront\Pagelet\Header\HeaderPageletLoader;
use Shopwell\Storefront\Pagelet\Menu\Offcanvas\MenuOffcanvasPageletLoader;
use Shopwell\Storefront\Pagelet\Wishlist\GuestWishlistPageletLoader;
use Shopwell\Storefront\Theme\ResolvedConfigLoader;
use Shopwell\Storefront\Theme\StorefrontPluginConfiguration\StorefrontPluginConfigurationFactory;
use Shopwell\Storefront\Theme\ThemeConfigValueAccessor;
use Shopwell\Storefront\Theme\ThemeRuntimeConfigService;
use Shopwell\Storefront\Theme\ThemeScripts;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouterInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('shopwell.twig.app_variable.allowed_server_params', ['server_name', 'request_uri', 'app_url', 'http_user_agent', 'http_host', 'server_name', 'server_port', 'redirect_url', 'https', 'forwarded', 'host', 'remote_addr', 'http_x_forwarded_for', 'http_x_forwarded_host', 'http_x_forwarded_proto', 'http_x_forwarded_port', 'http_x_forwarded_prefix']);

    $services->defaults()
        ->autowire();

    $services->set(StorefrontCartFacade::class)
        ->args([
            service(CartService::class),
            service(BlockedShippingMethodSwitcher::class),
            service(BlockedPaymentMethodSwitcher::class),
            service(ContextSwitchRoute::class),
            service(CartCalculator::class),
            service(CartPersister::class),
        ]);

    $services->set(CustomerGroupSubscriber::class)
        ->args([
            service('customer_group.repository'),
            service('seo_url.repository'),
            service('language.repository'),
            service(SeoUrlPersister::class),
            service('slugify'),
        ])
        ->tag('kernel.event_subscriber');

    $services->set(BlockedShippingMethodSwitcher::class)
        ->args([service(ShippingMethodRoute::class)]);

    $services->set(BlockedPaymentMethodSwitcher::class)
        ->args([service(PaymentMethodRoute::class)]);

    $services->set(CacheCookieEventSubscriber::class)
        ->args([service('session.factory')])
        ->tag('kernel.event_subscriber');

    $services->set(CachedDomainLoader::class)
        ->decorate(DomainLoader::class, null, -1000)
        ->args([
            service('Shopwell\Storefront\Framework\Routing\CachedDomainLoader.inner'),
            service('cache.object'),
            service('logger'),
        ]);

    $services->set(CachedDomainLoaderInvalidator::class)
        ->args([service(CacheInvalidator::class)])
        ->tag('kernel.event_subscriber');

    $services->set(DomainLoader::class)
        ->args([service(Connection::class)]);

    $services->set(RequestTransformer::class)
        ->public()
        ->decorate(RequestTransformerInterface::class)
        ->args([
            service('Shopwell\Storefront\Framework\Routing\RequestTransformer.inner'),
            service(SeoResolver::class),
            '%shopwell.routing.registered_api_prefixes%',
            service(DomainLoader::class),
        ]);

    $services->set(Router::class)
        ->decorate('router')
        ->args([
            service('Shopwell\Storefront\Framework\Routing\Router.inner'),
            service('request_stack'),
            '%storefront.router.allowed_routes%',
        ]);

    $services->set(MaintenanceModeResolver::class)
        ->args([
            service('request_stack'),
            service(\Shopwell\Core\Framework\Routing\MaintenanceModeResolver::class),
        ]);

    $services->set(StorefrontRouteScope::class)
        ->tag('shopwell.route_scope');

    $services->set(TemplateDataExtension::class)
        ->args([
            service('request_stack'),
            '%shopwell.staging.storefront.show_banner%',
            service(Connection::class),
        ])
        ->tag('twig.extension');

    $services->set(TemplateConfigAccessor::class)
        ->args([
            service(SystemConfigService::class),
            service(ThemeConfigValueAccessor::class),
            service(ThemeScripts::class),
        ]);

    $services->set(ThemeConfigValueAccessor::class)
        ->args([
            service(ResolvedConfigLoader::class),
            service(CacheTagCollector::class),
        ]);

    $services->set(ConfigExtension::class)
        ->args([service(TemplateConfigAccessor::class)])
        ->tag('twig.extension');

    $services->set(IconExtension::class)
        ->tag('twig.extension');

    $services->set(ThumbnailExtension::class)
        ->args([service(TemplateFinder::class)])
        ->tag('twig.extension');

    $services->set(TwigDateRequestListener::class)
        ->args([service('service_container')])
        ->tag('kernel.event_listener', ['event' => 'kernel.request']);

    $services->set(ErrorTemplateResolver::class)
        ->private()
        ->args([service('twig')]);

    $services->set(UrlEncodingTwigFilter::class)
        ->private()
        ->tag('twig.extension');

    $services->set(IconCacheTwigFilter::class)
        ->private()
        ->tag('twig.extension');

    $services->set(StorefrontMediaUploader::class)
        ->args([
            service(MediaService::class),
            service(FileSaver::class),
            service(StorefrontMediaValidatorRegistry::class),
        ]);

    $services->set(StorefrontMediaValidatorRegistry::class)
        ->public()
        ->args([tagged_iterator('storefront.media.upload.validator')]);

    $services->set(StorefrontMediaImageValidator::class)
        ->tag('storefront.media.upload.validator');

    $services->set(StorefrontMediaDocumentValidator::class)
        ->tag('storefront.media.upload.validator');

    $services->set(StorefrontSubscriber::class)
        ->args([
            service('request_stack'),
            service('router'),
            service(MaintenanceModeResolver::class),
            service(SystemConfigService::class),
            service('event_dispatcher'),
        ])
        ->tag('kernel.event_subscriber');

    $services->set(TemplateDataSubscriber::class)
        ->args([
            service(HreflangLoaderInterface::class),
            service(ShopIdProvider::class),
            service(ActiveAppsLoader::class),
            service(ThemeRuntimeConfigService::class),
        ])
        ->tag('kernel.event_subscriber');

    $services->set(CanonicalLinkListener::class)
        ->tag('kernel.event_listener', ['event' => BeforeSendResponseEvent::class]);

    $services->set(NotFoundSubscriber::class)
        ->args([
            service('http_kernel'),
            service(SalesChannelContextService::class),
            '%kernel.debug%',
            service('cache.object'),
            service(EntityCacheKeyGenerator::class),
            service(CacheInvalidator::class),
            service('event_dispatcher'),
            '%session.storage.options%',
        ])
        ->tag('kernel.event_subscriber')
        ->tag('kernel.reset', ['method' => 'reset']);

    $services->set(AffiliateTrackingListener::class)
        ->tag('kernel.event_subscriber');

    $services->set(NavigationPageLoader::class)
        ->args([
            service(GenericPageLoader::class),
            service('event_dispatcher'),
            service(CategoryRoute::class),
            service(SeoUrlPlaceholderHandlerInterface::class),
        ]);

    $services->set(ErrorPageLoader::class)
        ->args([
            service(GenericPageLoader::class),
            service('event_dispatcher'),
        ]);

    $services->set(LandingPageLoader::class)
        ->args([
            service(GenericPageLoader::class),
            service(LandingPageRoute::class),
            service('event_dispatcher'),
        ]);

    $services->set(MenuOffcanvasPageletLoader::class)
        ->args([
            service('event_dispatcher'),
            service(NavigationLoader::class),
        ]);

    $services->set(BasicCaptchaPageletLoader::class)
        ->args([
            service('event_dispatcher'),
            service(BasicCaptchaGenerator::class),
            service(NavigationLoader::class),
        ]);

    $services->set(CountryStateDataPageletLoader::class)
        ->args([
            service(CountryStateRoute::class),
            service('event_dispatcher'),
        ]);

    $services->set(SuggestPageLoader::class)
        ->args([
            service('event_dispatcher'),
            service(ProductSuggestRoute::class),
            service(GenericPageLoader::class),
        ]);

    $services->set(HeaderPageletLoader::class)
        ->args([
            service('event_dispatcher'),
            service(CurrencyRoute::class),
            service(LanguageRoute::class),
            service(NavigationLoader::class),
        ]);

    $services->set(FooterPageletLoader::class)
        ->args([
            service('event_dispatcher'),
            service(NavigationLoader::class),
            service(PaymentMethodRoute::class),
            service(ShippingMethodRoute::class),
        ]);

    $services->set(GenericPageLoader::class)
        ->args([
            service(SystemConfigService::class),
            service('event_dispatcher'),
        ]);

    $services->set(SearchPageLoader::class)
        ->public()
        ->args([
            service(GenericPageLoader::class),
            service(ProductSearchRoute::class),
            service('event_dispatcher'),
            service(Translator::class),
        ]);

    $services->set(ProductPageLoader::class)
        ->args([
            service(GenericPageLoader::class),
            service('event_dispatcher'),
            service(ProductDetailRoute::class),
        ]);

    $services->set(MinimalQuickViewPageLoader::class)
        ->args([
            service('event_dispatcher'),
            service(ProductDetailRoute::class),
        ]);

    $services->set(ProductPageConfiguratorLoader::class)
        ->decorate(ProductConfiguratorLoader::class)
        ->args([service('Shopwell\Storefront\Page\Product\Configurator\ProductPageConfiguratorLoader.inner')]);

    $services->set(CheckoutFinishPageLoader::class)
        ->args([
            service('event_dispatcher'),
            service(GenericPageLoader::class),
            service(OrderRoute::class),
            service(Translator::class),
            service(SystemConfigService::class),
        ]);

    $services->set(CheckoutConfirmPageLoader::class)
        ->args([
            service('event_dispatcher'),
            service(StorefrontCartFacade::class),
            service(CheckoutGatewayRoute::class),
            service(GenericPageLoader::class),
            service(AddressValidationFactory::class),
            service(DataValidator::class),
            service(Translator::class),
        ]);

    $services->set(CheckoutCartPageLoader::class)
        ->args([
            service(GenericPageLoader::class),
            service('event_dispatcher'),
            service(StorefrontCartFacade::class),
            service(CheckoutGatewayRoute::class),
            service(CountryRoute::class),
            service(Translator::class),
        ]);

    $services->set(OffcanvasCartPageLoader::class)
        ->args([
            service('event_dispatcher'),
            service(StorefrontCartFacade::class),
            service(GenericPageLoader::class),
            service(ShippingMethodRoute::class),
        ]);

    $services->set(AccountProfilePageLoader::class)
        ->args([
            service(GenericPageLoader::class),
            service('event_dispatcher'),
            service(Translator::class),
        ]);

    $services->set(AccountOverviewPageLoader::class)
        ->args([
            service(GenericPageLoader::class),
            service('event_dispatcher'),
            service(OrderRoute::class),
            service(CustomerRoute::class),
            service(Translator::class),
        ]);

    $services->set(AccountOrderPageLoader::class)
        ->args([
            service(GenericPageLoader::class),
            service('event_dispatcher'),
            service(OrderRoute::class),
            service(Translator::class),
        ]);

    $services->set(AccountEditOrderPageLoader::class)
        ->args([
            service(GenericPageLoader::class),
            service('event_dispatcher'),
            service(OrderRoute::class),
            service(CheckoutGatewayRoute::class),
            service(OrderConverter::class),
            service(OrderService::class),
            service(Translator::class),
            service(CartService::class),
        ]);

    $services->set(AccountLoginPageLoader::class)
        ->args([
            service(GenericPageLoader::class),
            service('event_dispatcher'),
            service(CountryRoute::class),
            service(Translator::class),
        ]);

    $services->set(AccountRecoverPasswordPageLoader::class)
        ->args([
            service(GenericPageLoader::class),
            service('event_dispatcher'),
            service(CustomerRecoveryIsExpiredRoute::class),
        ]);

    $services->set(CustomerGroupRegistrationPageLoader::class)
        ->args([
            service(AccountLoginPageLoader::class),
            service(CustomerGroupRegistrationSettingsRoute::class),
            service('event_dispatcher'),
        ]);

    $services->set(CheckoutRegisterPageLoader::class)
        ->public()
        ->args([
            service(GenericPageLoader::class),
            service(ListAddressRoute::class),
            service('event_dispatcher'),
            service(CartService::class),
            service(CountryRoute::class),
            service(Translator::class),
        ]);

    $services->set(AddressDetailPageLoader::class)
        ->args([
            service(GenericPageLoader::class),
            service(CountryRoute::class),
            service('event_dispatcher'),
            service(ListAddressRoute::class),
            service(Translator::class),
        ]);

    $services->set(AddressListingPageLoader::class)
        ->args([
            service(GenericPageLoader::class),
            service(CountryRoute::class),
            service(ListAddressRoute::class),
            service('event_dispatcher'),
            service(CartService::class),
            service(Translator::class),
        ]);

    $services->set(SitemapPageLoader::class)
        ->args([
            service('event_dispatcher'),
            service(SitemapRoute::class),
        ]);

    $services->set(SalesChannelCreateStorefrontCommand::class)
        ->args([
            service('snippet_set.repository'),
            service(SalesChannelCreator::class),
        ])
        ->tag('console.command');

    $services->set(ResponseHeaderListener::class)
        ->tag('kernel.event_subscriber');

    $services->set(CartMergedSubscriber::class)
        ->args([
            service('translator'),
            service('request_stack'),
        ])
        ->tag('kernel.event_subscriber');

    $services->set(WishlistPageLoader::class)
        ->args([
            service(GenericPageLoader::class),
            service(LoadWishlistRoute::class),
            service('event_dispatcher'),
        ]);

    $services->set(GuestWishlistPageLoader::class)
        ->args([
            service(GenericPageLoader::class),
            service('event_dispatcher'),
        ]);

    $services->set(GuestWishlistPageletLoader::class)
        ->args([
            service(ProductListRoute::class),
            service(SystemConfigService::class),
            service('event_dispatcher'),
            service(ProductCloseoutFilterFactory::class),
        ]);

    $services->set(IconTemplateLoader::class)
        ->decorate(TemplateLoader::class)
        ->args([
            service('Shopwell\Storefront\Framework\App\Template\IconTemplateLoader.inner'),
            service(StorefrontPluginConfigurationFactory::class),
            service(SourceResolver::class),
            '%kernel.project_dir%',
        ]);

    $services->set(TwigAppVariable::class)
        ->decorate('twig.app_variable')
        ->args([
            service('Shopwell\Storefront\Framework\Twig\TwigAppVariable.inner'),
            '%shopwell.twig.app_variable.allowed_server_params%',
        ]);

    $services->set(DomainNotMappedListener::class)
        ->args([service('service_container')])
        ->tag('kernel.event_listener', ['event' => 'kernel.exception']);

    $services->set(SalesChannelDomainUtil::class)
        ->args([
            service(RouterInterface::class),
            service(RequestStack::class),
            service(KernelInterface::class),
            service('logger'),
        ]);

    $services->set(SalesChannelsReadinessCheck::class)
        ->args([
            service(SalesChannelDomainUtil::class),
            service(SalesChannelDomainProvider::class),
        ])
        ->tag('shopwell.system_check');

    $services->set(ProductDetailReadinessCheck::class)
        ->args([
            service(SalesChannelDomainUtil::class),
            service(Connection::class),
            service(SalesChannelDomainProvider::class),
        ])
        ->tag('shopwell.system_check');

    $services->set(ProductListingReadinessCheck::class)
        ->args([
            service(SalesChannelDomainUtil::class),
            service(Connection::class),
            service(SalesChannelDomainProvider::class),
        ])
        ->tag('shopwell.system_check');

    $services->set(SalesChannelDomainProvider::class)
        ->args([service(Connection::class)]);

    $services->set(RobotsDirectiveParser::class);

    $services->set(RobotsPageLoader::class)
        ->args([
            service('event_dispatcher'),
            service('sales_channel_domain.repository'),
            service(SystemConfigService::class),
            service(RobotsDirectiveParser::class),
        ]);

    $services->set(RobotsConfigChangeSubscriber::class)
        ->tag('kernel.event_subscriber');

    $services->set(RobotsRouteScopeWhitelist::class)
        ->tag('shopwell.route_scope_whitelist');
};
