<?php declare(strict_types=1);

use Doctrine\DBAL\Connection;
use Shopwell\Core\Checkout\Cart\CartRuleLoader;
use Shopwell\Core\Content\Category\CategoryDefinition;
use Shopwell\Core\Content\Product\ProductDefinition;
use Shopwell\Core\Content\Sitemap\Commands\SitemapGenerateCommand;
use Shopwell\Core\Content\Sitemap\ConfigHandler\File;
use Shopwell\Core\Content\Sitemap\Provider\CategoryUrlProvider;
use Shopwell\Core\Content\Sitemap\Provider\CustomUrlProvider;
use Shopwell\Core\Content\Sitemap\Provider\HomeUrlProvider;
use Shopwell\Core\Content\Sitemap\Provider\LandingPageUrlProvider;
use Shopwell\Core\Content\Sitemap\Provider\ProductUrlProvider;
use Shopwell\Core\Content\Sitemap\SalesChannel\SitemapFileRoute;
use Shopwell\Core\Content\Sitemap\SalesChannel\SitemapRoute;
use Shopwell\Core\Content\Sitemap\ScheduledTask\SitemapGenerateTask;
use Shopwell\Core\Content\Sitemap\ScheduledTask\SitemapGenerateTaskHandler;
use Shopwell\Core\Content\Sitemap\ScheduledTask\SitemapMessageHandler;
use Shopwell\Core\Content\Sitemap\Service\ConfigHandler;
use Shopwell\Core\Content\Sitemap\Service\SitemapExporter;
use Shopwell\Core\Content\Sitemap\Service\SitemapHandleFactory;
use Shopwell\Core\Content\Sitemap\Service\SitemapHandleFactoryInterface;
use Shopwell\Core\Content\Sitemap\Service\SitemapLister;
use Shopwell\Core\Framework\Adapter\Cache\CacheTagCollector;
use Shopwell\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory;
use Shopwell\Core\Framework\Extensions\ExtensionDispatcher;
use Shopwell\Core\System\SalesChannel\Context\SalesChannelContextFactory;
use Shopwell\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(SitemapExporter::class)
        ->args([
            tagged_iterator('shopwell.sitemap_url_provider'),
            service('cache.system'),
            '%shopwell.sitemap.batchsize%',
            service('shopwell.filesystem.sitemap'),
            service(SitemapHandleFactoryInterface::class),
            service('event_dispatcher'),
            service(CartRuleLoader::class),
        ]);

    $services->set(SitemapLister::class)
        ->args([
            service('shopwell.filesystem.sitemap'),
            service('shopwell.asset.sitemap'),
        ]);

    $services->set(ConfigHandler::class)
        ->args([tagged_iterator('shopwell.sitemap.config_handler')]);

    $services->set(SitemapHandleFactoryInterface::class, SitemapHandleFactory::class)
        ->args([service('event_dispatcher')]);

    $services->set(SitemapRoute::class)
        ->public()
        ->args([
            service(SitemapLister::class),
            service(SystemConfigService::class),
            service(SitemapExporter::class),
            service(CacheTagCollector::class),
        ]);

    $services->set(SitemapFileRoute::class)
        ->public()
        ->args([
            service('shopwell.filesystem.sitemap'),
            service(ExtensionDispatcher::class),
        ]);

    $services->set(HomeUrlProvider::class)
        ->tag('shopwell.sitemap_url_provider');

    $services->set(CategoryUrlProvider::class)
        ->args([
            service(ConfigHandler::class),
            service(Connection::class),
            service(CategoryDefinition::class),
            service(IteratorFactory::class),
            service('router'),
            service('event_dispatcher'),
        ])
        ->tag('shopwell.sitemap_url_provider');

    $services->set(CustomUrlProvider::class)
        ->args([service(ConfigHandler::class)])
        ->tag('shopwell.sitemap_url_provider');

    $services->set(ProductUrlProvider::class)
        ->args([
            service(ConfigHandler::class),
            service(Connection::class),
            service(ProductDefinition::class),
            service(IteratorFactory::class),
            service('router'),
            service(SystemConfigService::class),
            service('event_dispatcher'),
        ])
        ->tag('shopwell.sitemap_url_provider');

    $services->set(LandingPageUrlProvider::class)
        ->args([
            service(ConfigHandler::class),
            service(Connection::class),
            service('router'),
            service('event_dispatcher'),
        ])
        ->tag('shopwell.sitemap_url_provider');

    $services->set(File::class)
        ->args(['%shopwell.sitemap%'])
        ->tag('shopwell.sitemap.config_handler');

    $services->set(SitemapGenerateCommand::class)
        ->args([
            service('sales_channel.repository'),
            service(SitemapExporter::class),
            service(SalesChannelContextFactory::class),
            service('event_dispatcher'),
        ])
        ->tag('console.command');

    $services->set(SitemapGenerateTask::class)
        ->tag('shopwell.scheduled.task');

    $services->set(SitemapGenerateTaskHandler::class)
        ->args([
            service('scheduled_task.repository'),
            service('logger'),
            service('sales_channel.repository'),
            service(SystemConfigService::class),
            service('messenger.default_bus'),
            service('event_dispatcher'),
        ])
        ->tag('messenger.message_handler');

    $services->set(SitemapMessageHandler::class)
        ->args([
            service(SalesChannelContextFactory::class),
            service(SitemapExporter::class),
            service('logger'),
            service(SystemConfigService::class),
        ])
        ->tag('messenger.message_handler');
};
