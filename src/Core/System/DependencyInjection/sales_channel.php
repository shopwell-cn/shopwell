<?php declare(strict_types=1);
/**
 * @codeCoverageIgnore - DI wiring only
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Checkout\Cart\CartPersister;
use Shopwell\Core\Checkout\Cart\CartRuleLoader;
use Shopwell\Core\Checkout\Cart\Order\OrderConverter;
use Shopwell\Core\Checkout\Cart\SalesChannel\CartService;
use Shopwell\Core\Checkout\Cart\Tax\TaxDetector;
use Shopwell\Core\Checkout\Customer\SalesChannel\AccountService;
use Shopwell\Core\Checkout\Customer\SalesChannel\RegisterRoute;
use Shopwell\Core\Content\Media\MediaUrlPlaceholderHandlerInterface;
use Shopwell\Core\Content\Seo\SeoUrlPlaceholderHandlerInterface;
use Shopwell\Core\Framework\Api\ApiDefinition\DefinitionService;
use Shopwell\Core\Framework\Api\Route\ApiRouteInfoResolver;
use Shopwell\Core\Framework\App\Context\Gateway\AppContextGateway;
use Shopwell\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory;
use Shopwell\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopwell\Core\Framework\DataAbstractionLayer\Indexing\ManyToManyIdFieldUpdater;
use Shopwell\Core\Framework\Gateway\Context\Command\Executor\ContextGatewayCommandExecutor;
use Shopwell\Core\Framework\Gateway\Context\Command\Executor\ContextGatewayCommandValidator;
use Shopwell\Core\Framework\Gateway\Context\Command\Handler\AddCustomerMessageCommandHandler;
use Shopwell\Core\Framework\Gateway\Context\Command\Handler\ChangeAddressCommandHandler;
use Shopwell\Core\Framework\Gateway\Context\Command\Handler\ChangeCheckoutOptionsCommandHandler;
use Shopwell\Core\Framework\Gateway\Context\Command\Handler\ChangeCurrencyCommandHandler;
use Shopwell\Core\Framework\Gateway\Context\Command\Handler\ChangeLanguageCommandHandler;
use Shopwell\Core\Framework\Gateway\Context\Command\Handler\ChangeShippingLocationCommandHandler;
use Shopwell\Core\Framework\Gateway\Context\Command\Handler\LoginCustomerCommandHandler;
use Shopwell\Core\Framework\Gateway\Context\Command\Handler\RegisterCustomerCommandHandler;
use Shopwell\Core\Framework\Gateway\Context\Command\Registry\ContextGatewayCommandRegistry;
use Shopwell\Core\Framework\Gateway\Context\SalesChannel\ContextGatewayRoute;
use Shopwell\Core\Framework\Log\ExceptionLogger;
use Shopwell\Core\Framework\Validation\DataValidator;
use Shopwell\Core\System\SalesChannel\Aggregate\SalesChannelAnalytics\SalesChannelAnalyticsDefinition;
use Shopwell\Core\System\SalesChannel\Aggregate\SalesChannelCountry\SalesChannelCountryDefinition;
use Shopwell\Core\System\SalesChannel\Aggregate\SalesChannelCurrency\SalesChannelCurrencyDefinition;
use Shopwell\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainDefinition;
use Shopwell\Core\System\SalesChannel\Aggregate\SalesChannelLanguage\SalesChannelLanguageDefinition;
use Shopwell\Core\System\SalesChannel\Aggregate\SalesChannelPaymentMethod\SalesChannelPaymentMethodDefinition;
use Shopwell\Core\System\SalesChannel\Aggregate\SalesChannelShippingMethod\SalesChannelShippingMethodDefinition;
use Shopwell\Core\System\SalesChannel\Aggregate\SalesChannelTranslation\SalesChannelTranslationDefinition;
use Shopwell\Core\System\SalesChannel\Aggregate\SalesChannelType\SalesChannelTypeDefinition;
use Shopwell\Core\System\SalesChannel\Aggregate\SalesChannelTypeTranslation\SalesChannelTypeTranslationDefinition;
use Shopwell\Core\System\SalesChannel\Api\StoreApiResponseListener;
use Shopwell\Core\System\SalesChannel\Api\StructEncoder;
use Shopwell\Core\System\SalesChannel\Context\BaseSalesChannelContextFactory;
use Shopwell\Core\System\SalesChannel\Context\CachedBaseSalesChannelContextFactory;
use Shopwell\Core\System\SalesChannel\Context\CachedSalesChannelContextFactory;
use Shopwell\Core\System\SalesChannel\Context\CartRestorer;
use Shopwell\Core\System\SalesChannel\Context\Cleanup\CleanupSalesChannelContextTask;
use Shopwell\Core\System\SalesChannel\Context\Cleanup\CleanupSalesChannelContextTaskHandler;
use Shopwell\Core\System\SalesChannel\Context\ContextFactory;
use Shopwell\Core\System\SalesChannel\Context\SalesChannelContextFactory;
use Shopwell\Core\System\SalesChannel\Context\SalesChannelContextPersister;
use Shopwell\Core\System\SalesChannel\Context\SalesChannelContextRestorer;
use Shopwell\Core\System\SalesChannel\Context\SalesChannelContextService;
use Shopwell\Core\System\SalesChannel\Context\SalesChannelContextValueResolver;
use Shopwell\Core\System\SalesChannel\Cookie\AnalyticsCookieCollectListener;
use Shopwell\Core\System\SalesChannel\DataAbstractionLayer\SalesChannelIndexer;
use Shopwell\Core\System\SalesChannel\Entity\DefinitionRegistryChain;
use Shopwell\Core\System\SalesChannel\Entity\SalesChannelDefinitionInstanceRegistry;
use Shopwell\Core\System\SalesChannel\SalesChannel\ContextRoute;
use Shopwell\Core\System\SalesChannel\SalesChannel\ContextSwitchRoute;
use Shopwell\Core\System\SalesChannel\SalesChannel\StoreApiInfoController;
use Shopwell\Core\System\SalesChannel\SalesChannelDefinition;
use Shopwell\Core\System\SalesChannel\StoreApiCustomFieldMapper;
use Shopwell\Core\System\SalesChannel\Subscriber\SalesChannelTypeValidator;
use Shopwell\Core\System\SalesChannel\Validation\SalesChannelValidator;
use Symfony\Component\HttpFoundation\RequestStack;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(SalesChannelDefinition::class)
        ->tag('shopwell.entity.definition')
        ->tag('shopwell.entity.hookable');

    $services->set(SalesChannelTranslationDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(SalesChannelCountryDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(SalesChannelCurrencyDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(SalesChannelDomainDefinition::class)
        ->tag('shopwell.entity.definition')
        ->tag('shopwell.entity.hookable');

    $services->set(SalesChannelLanguageDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(SalesChannelPaymentMethodDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(SalesChannelShippingMethodDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(SalesChannelTypeDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(SalesChannelTypeTranslationDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(SalesChannelAnalyticsDefinition::class)
        ->tag('shopwell.entity.definition', ['entity' => 'sales_channel_analytics']);

    $services->set(SalesChannelContextPersister::class)
        ->args([
            service(Connection::class),
            service('event_dispatcher'),
            service(CartPersister::class),
            '%shopwell.api.store.context_lifetime%',
        ]);

    $services->set(SalesChannelContextFactory::class)
        ->public()
        ->args([
            service('customer.repository'),
            service('customer_group.repository'),
            service('customer_address.repository'),
            service('payment_method.repository'),
            service(TaxDetector::class),
            tagged_iterator('tax.rule_type_filter'),
            service('event_dispatcher'),
            service('currency_country_rounding.repository'),
            service(BaseSalesChannelContextFactory::class),
        ]);

    $services->set(BaseSalesChannelContextFactory::class)
        ->args([
            service('sales_channel.repository'),
            service('currency.repository'),
            service('customer_group.repository'),
            service('country.repository'),
            service('tax.repository'),
            service('payment_method.repository'),
            service('shipping_method.repository'),
            service('country_state.repository'),
            service('currency_country_rounding.repository'),
            service(ContextFactory::class),
            service('language.repository'),
        ]);

    $services->set(ContextFactory::class)
        ->args([
            service(Connection::class),
            service('event_dispatcher'),
        ]);

    $services->set(CachedBaseSalesChannelContextFactory::class)
        ->decorate(BaseSalesChannelContextFactory::class)
        ->args([
            service('.inner'),
            service('cache.object'),
        ]);

    $services->set(CachedSalesChannelContextFactory::class)
        ->public()
        ->decorate(SalesChannelContextFactory::class, null, -1000)
        ->args([
            service('.inner'),
            service('cache.object'),
        ]);

    $services->set(SalesChannelContextService::class)
        ->args([
            service(SalesChannelContextFactory::class),
            service(CartRuleLoader::class),
            service(SalesChannelContextPersister::class),
            service(CartService::class),
            service('event_dispatcher'),
            service(RequestStack::class),
        ]);

    $services->set(SalesChannelContextRestorer::class)
        ->args([
            service(SalesChannelContextFactory::class),
            service(CartRuleLoader::class),
            service(OrderConverter::class),
            service('order.repository'),
            service(Connection::class),
            service('event_dispatcher'),
        ]);

    $services->set(CartRestorer::class)
        ->args([
            service(SalesChannelContextFactory::class),
            service(SalesChannelContextPersister::class),
            service(CartService::class),
            service(CartRuleLoader::class),
            service(CartPersister::class),
            service('event_dispatcher'),
            service(RequestStack::class),
        ]);

    $services->set(StoreApiInfoController::class)
        ->public()
        ->args([
            service(DefinitionService::class),
            service('twig'),
            '%shopwell.security.csp_templates%',
            service(ApiRouteInfoResolver::class),
        ]);

    $services->set(ContextSwitchRoute::class)
        ->public()
        ->args([
            service(DataValidator::class),
            service(SalesChannelContextPersister::class),
            service('event_dispatcher'),
            service(SalesChannelContextService::class),
        ]);

    $services->set(ContextRoute::class)
        ->public();

    $services->set(SalesChannelDefinitionInstanceRegistry::class)
        ->public()
        ->args([
            '',
            service('service_container'),
            [],
            [],
        ]);

    $services->set(DefinitionRegistryChain::class)
        ->args([
            service(DefinitionInstanceRegistry::class),
            service(SalesChannelDefinitionInstanceRegistry::class),
        ]);

    $services->set(SalesChannelContextValueResolver::class)
        ->tag('controller.argument_value_resolver', ['priority' => 1000]);

    $services->set(StoreApiResponseListener::class)
        ->args([
            service(StructEncoder::class),
            service('event_dispatcher'),
            service(SeoUrlPlaceholderHandlerInterface::class),
            service(MediaUrlPlaceholderHandlerInterface::class),
        ])
        ->tag('kernel.event_subscriber');

    $services->set(StructEncoder::class)
        ->args([
            service(DefinitionRegistryChain::class),
            service('serializer'),
            service(Connection::class),
        ])
        ->tag('kernel.reset', ['method' => 'reset']);

    $services->set(SalesChannelIndexer::class)
        ->args([
            service(IteratorFactory::class),
            service('sales_channel.repository'),
            service('event_dispatcher'),
            service(ManyToManyIdFieldUpdater::class),
        ])
        ->tag('shopwell.entity_indexer');

    $services->set(CleanupSalesChannelContextTask::class)
        ->tag('shopwell.scheduled.task');

    $services->set(CleanupSalesChannelContextTaskHandler::class)
        ->args([
            service('scheduled_task.repository'),
            service('logger'),
            service(Connection::class),
            '%shopwell.sales_channel_context.expire_days%',
        ])
        ->tag('messenger.message_handler');

    $services->set(SalesChannelValidator::class)
        ->args([service(Connection::class)])
        ->tag('kernel.event_subscriber');

    $services->set(SalesChannelTypeValidator::class)
        ->tag('kernel.event_subscriber');

    $services->set(AnalyticsCookieCollectListener::class)
        ->args([service('sales_channel_analytics.repository')])
        ->tag('kernel.event_listener');

    $services->set(StoreApiCustomFieldMapper::class)
        ->args([service(Connection::class)])
        ->tag('kernel.reset', ['method' => 'reset']);

    $services->set(ContextGatewayRoute::class)
        ->public()
        ->args([service(AppContextGateway::class)]);

    $services->set(ContextGatewayCommandValidator::class)
        ->args([service(ExceptionLogger::class)]);

    $services->set(ContextGatewayCommandExecutor::class)
        ->args([
            service(ContextSwitchRoute::class),
            service(ContextGatewayCommandRegistry::class),
            service(ContextGatewayCommandValidator::class),
            service(ExceptionLogger::class),
            service(SalesChannelContextService::class),
        ]);

    $services->set(ContextGatewayCommandRegistry::class)
        ->args([tagged_iterator('shopwell.context.gateway.command')]);

    $services->set(AddCustomerMessageCommandHandler::class)
        ->tag('shopwell.context.gateway.command');

    $services->set(ChangeAddressCommandHandler::class)
        ->tag('shopwell.context.gateway.command');

    $services->set(ChangeCheckoutOptionsCommandHandler::class)
        ->args([
            service('payment_method.repository'),
            service('shipping_method.repository'),
        ])
        ->tag('shopwell.context.gateway.command');

    $services->set(ChangeCurrencyCommandHandler::class)
        ->args([service('currency.repository')])
        ->tag('shopwell.context.gateway.command');

    $services->set(ChangeLanguageCommandHandler::class)
        ->args([service('language.repository')])
        ->tag('shopwell.context.gateway.command');

    $services->set(ChangeShippingLocationCommandHandler::class)
        ->args([
            service('country.repository'),
            service('country_state.repository'),
        ])
        ->tag('shopwell.context.gateway.command');

    $services->set(LoginCustomerCommandHandler::class)
        ->args([service(AccountService::class)])
        ->tag('shopwell.context.gateway.command');

    $services->set(RegisterCustomerCommandHandler::class)
        ->args([service(RegisterRoute::class)])
        ->tag('shopwell.context.gateway.command');
};
