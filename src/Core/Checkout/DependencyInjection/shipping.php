<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Checkout\Shipping\Aggregate\ShippingMethodPrice\ShippingMethodPriceDefinition;
use Shopwell\Core\Checkout\Shipping\Aggregate\ShippingMethodPrice\ShippingMethodPriceExceptionHandler;
use Shopwell\Core\Checkout\Shipping\Aggregate\ShippingMethodTag\ShippingMethodTagDefinition;
use Shopwell\Core\Checkout\Shipping\Aggregate\ShippingMethodTranslation\ShippingMethodTranslationDefinition;
use Shopwell\Core\Checkout\Shipping\SalesChannel\SalesChannelShippingMethodDefinition;
use Shopwell\Core\Checkout\Shipping\SalesChannel\ShippingMethodRoute;
use Shopwell\Core\Checkout\Shipping\ShippingMethodDefinition;
use Shopwell\Core\Checkout\Shipping\Validator\ShippingMethodValidator;
use Shopwell\Core\Framework\Adapter\Cache\CacheTagCollector;
use Shopwell\Core\Framework\Rule\RuleIdMatcher;
use Shopwell\Core\Framework\Script\Execution\ScriptExecutor;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set(ShippingMethodPriceExceptionHandler::class)
        ->tag('shopwell.dal.exception_handler');

    $services->set(ShippingMethodDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(SalesChannelShippingMethodDefinition::class)
        ->tag('shopwell.sales_channel.entity.definition');

    $services->set(ShippingMethodTagDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(ShippingMethodPriceDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(ShippingMethodTranslationDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(ShippingMethodRoute::class)
        ->public()
        ->args([
            service('sales_channel.shipping_method.repository'),
            service(CacheTagCollector::class),
            service(ScriptExecutor::class),
            service(RuleIdMatcher::class),
        ]);

    $services->set(ShippingMethodValidator::class)
        ->args([service(Connection::class)])
        ->tag('kernel.event_subscriber');
};
