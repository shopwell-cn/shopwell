<?php declare(strict_types=1);

use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Shopwell\Core\Content\Media\File\FileNameProvider;
use Shopwell\Core\Content\Media\File\FileSaver;
use Shopwell\Core\Framework\Adapter\Cache\CacheInvalidator;
use Shopwell\Core\Framework\Adapter\Filesystem\Plugin\CopyBatchInputFactory;
use Shopwell\Core\Framework\App\ActiveAppsLoader;
use Shopwell\Core\Framework\App\Source\SourceResolver;
use Shopwell\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory;
use Shopwell\Core\Framework\Notification\NotificationService;
use Shopwell\Core\Framework\Plugin\KernelPluginLoader\KernelPluginLoader;
use Shopwell\Core\System\SystemConfig\Service\ConfigurationService;
use Shopwell\Core\System\SystemConfig\SystemConfigService;
use Shopwell\Storefront\Theme\AbstractThemePathBuilder;
use Shopwell\Storefront\Theme\Aggregate\ThemeChildDefinition;
use Shopwell\Storefront\Theme\Aggregate\ThemeMediaDefinition;
use Shopwell\Storefront\Theme\Aggregate\ThemeSalesChannelDefinition;
use Shopwell\Storefront\Theme\Aggregate\ThemeTranslationDefinition;
use Shopwell\Storefront\Theme\Command\ThemeChangeCommand;
use Shopwell\Storefront\Theme\Command\ThemeCompileCommand;
use Shopwell\Storefront\Theme\Command\ThemeCreateCommand;
use Shopwell\Storefront\Theme\Command\ThemeDumpCommand;
use Shopwell\Storefront\Theme\Command\ThemePrepareIconsCommand;
use Shopwell\Storefront\Theme\Command\ThemeRefreshCommand;
use Shopwell\Storefront\Theme\ConfigLoader\AbstractAvailableThemeProvider;
use Shopwell\Storefront\Theme\ConfigLoader\AbstractConfigLoader;
use Shopwell\Storefront\Theme\ConfigLoader\DatabaseAvailableThemeProvider;
use Shopwell\Storefront\Theme\ConfigLoader\DatabaseConfigLoader;
use Shopwell\Storefront\Theme\ConfigLoader\StaticFileAvailableThemeProvider;
use Shopwell\Storefront\Theme\ConfigLoader\StaticFileConfigDumper;
use Shopwell\Storefront\Theme\ConfigLoader\StaticFileConfigLoader;
use Shopwell\Storefront\Theme\Controller\ThemeController;
use Shopwell\Storefront\Theme\DataAbstractionLayer\ThemeIndexer;
use Shopwell\Storefront\Theme\DatabaseSalesChannelThemeLoader;
use Shopwell\Storefront\Theme\Extension\LanguageExtension;
use Shopwell\Storefront\Theme\Extension\MediaExtension;
use Shopwell\Storefront\Theme\Extension\SalesChannelExtension;
use Shopwell\Storefront\Theme\MD5ThemePathBuilder;
use Shopwell\Storefront\Theme\Message\CompileThemeHandler;
use Shopwell\Storefront\Theme\ResolvedConfigLoader;
use Shopwell\Storefront\Theme\ScheduledTask\DeleteThemeFilesTask;
use Shopwell\Storefront\Theme\ScheduledTask\DeleteThemeFilesTaskHandler;
use Shopwell\Storefront\Theme\ScssPhpCompiler;
use Shopwell\Storefront\Theme\SeedingThemePathBuilder;
use Shopwell\Storefront\Theme\StorefrontPluginConfiguration\StorefrontPluginConfigurationFactory;
use Shopwell\Storefront\Theme\StorefrontPluginRegistry;
use Shopwell\Storefront\Theme\Subscriber\AppLifecycleSubscriber;
use Shopwell\Storefront\Theme\Subscriber\FirstRunWizardSubscriber;
use Shopwell\Storefront\Theme\Subscriber\PluginLifecycleSubscriber;
use Shopwell\Storefront\Theme\Subscriber\ThemeCompilerEnrichScssVarSubscriber;
use Shopwell\Storefront\Theme\Subscriber\ThemeSnippetsSubscriber;
use Shopwell\Storefront\Theme\Subscriber\UnusedMediaSubscriber;
use Shopwell\Storefront\Theme\Subscriber\UpdateSubscriber;
use Shopwell\Storefront\Theme\ThemeAppLifecycleHandler;
use Shopwell\Storefront\Theme\ThemeAssetPackage;
use Shopwell\Storefront\Theme\ThemeCompiler;
use Shopwell\Storefront\Theme\ThemeConfigCacheInvalidator;
use Shopwell\Storefront\Theme\ThemeDefinition;
use Shopwell\Storefront\Theme\ThemeFileResolver;
use Shopwell\Storefront\Theme\ThemeFilesystemResolver;
use Shopwell\Storefront\Theme\ThemeLifecycleHandler;
use Shopwell\Storefront\Theme\ThemeLifecycleService;
use Shopwell\Storefront\Theme\ThemeMergedConfigBuilder;
use Shopwell\Storefront\Theme\ThemeRuntimeConfigService;
use Shopwell\Storefront\Theme\ThemeRuntimeConfigStorage;
use Shopwell\Storefront\Theme\ThemeScripts;
use Shopwell\Storefront\Theme\ThemeService;
use Shopwell\Storefront\Theme\Twig\ThemeInheritanceBuilder;
use Shopwell\Storefront\Theme\Twig\ThemeInheritanceBuilderInterface;
use Shopwell\Storefront\Theme\Twig\ThemeNamespaceHierarchyBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(StorefrontPluginConfigurationFactory::class)
        ->args([
            service(KernelPluginLoader::class),
            service(SourceResolver::class),
            service(Filesystem::class),
        ]);

    $services->set(StorefrontPluginRegistry::class)
        ->public()
        ->args([
            service('kernel'),
            service(StorefrontPluginConfigurationFactory::class),
            service(ActiveAppsLoader::class),
        ])
        ->tag('kernel.reset', ['method' => 'reset']);

    $services->set(ScssPhpCompiler::class);

    $services->set(ThemeCompiler::class)
        ->args([
            service('shopwell.filesystem.theme'),
            service('shopwell.filesystem.temp'),
            service(CopyBatchInputFactory::class),
            service(ThemeFileResolver::class),
            '%kernel.debug%',
            service(EventDispatcherInterface::class),
            service(ThemeFilesystemResolver::class),
            tagged_iterator('shopwell.asset'),
            service(CacheInvalidator::class),
            service(LoggerInterface::class),
            service(AbstractThemePathBuilder::class),
            service(ScssPhpCompiler::class),
            '%storefront.theme.allowed_scss_values%',
            '%storefront.theme.validate_on_compile%',
            '%shopwell.filesystem.theme.visibility%',
        ]);

    $services->set(ThemeLifecycleService::class)
        ->args([
            service(StorefrontPluginRegistry::class),
            service('theme.repository'),
            service('media.repository'),
            service('media_folder.repository'),
            service('theme_media.repository'),
            service(FileSaver::class),
            service(FileNameProvider::class),
            service(ThemeFilesystemResolver::class),
            service('language.repository'),
            service('theme_child.repository'),
            service(Connection::class),
            service(StorefrontPluginConfigurationFactory::class)->nullOnInvalid(),
            service(ThemeRuntimeConfigService::class),
        ]);

    $services->set(ThemeFileResolver::class)
        ->args([service(ThemeFilesystemResolver::class)]);

    $services->set(ThemeScripts::class)
        ->args([
            service('request_stack'),
            service(ThemeRuntimeConfigService::class),
        ]);

    $services->set(ThemeMergedConfigBuilder::class)
        ->args([
            service(StorefrontPluginRegistry::class),
            service('theme.repository'),
        ]);

    $services->set(ThemeService::class)
        ->args([
            service(StorefrontPluginRegistry::class),
            service('theme.repository'),
            service('theme_sales_channel.repository'),
            service(ThemeCompiler::class),
            service(ScssPhpCompiler::class),
            service('event_dispatcher'),
            service(AbstractConfigLoader::class),
            service(Connection::class),
            service(SystemConfigService::class),
            service('messenger.default_bus'),
            service(NotificationService::class),
            service(ThemeMergedConfigBuilder::class),
            service(ThemeRuntimeConfigService::class),
        ]);

    $services->set(ResolvedConfigLoader::class)
        ->lazy()
        ->args([
            service('media.repository'),
            service(ThemeRuntimeConfigService::class),
        ]);

    $services->set(ThemeConfigCacheInvalidator::class)
        ->args([service(CacheInvalidator::class)])
        ->tag('kernel.event_subscriber');

    $services->set(ThemeLifecycleHandler::class)
        ->args([
            service(ThemeLifecycleService::class),
            service(ThemeService::class),
            service('theme.repository'),
            service(StorefrontPluginRegistry::class),
            service(Connection::class),
        ]);

    $services->set(ThemeAppLifecycleHandler::class)
        ->args([
            service(StorefrontPluginRegistry::class),
            service(StorefrontPluginConfigurationFactory::class),
            service(ThemeLifecycleHandler::class),
        ])
        ->tag('kernel.event_subscriber');

    $services->set(DatabaseAvailableThemeProvider::class)
        ->args([service(Connection::class)]);

    $services->set(DatabaseConfigLoader::class)
        ->args([
            service('theme.repository'),
            service(StorefrontPluginRegistry::class),
            service('media.repository'),
        ]);

    $services->set(ThemeRuntimeConfigStorage::class)
        ->args([service(Connection::class)]);

    $services->set(ThemeRuntimeConfigService::class)
        ->args([
            service(ThemeFileResolver::class),
            service(StorefrontPluginRegistry::class),
            service(ThemeMergedConfigBuilder::class),
            service(ThemeRuntimeConfigStorage::class),
        ]);

    $services->set(SeedingThemePathBuilder::class)
        ->lazy()
        ->args([service(SystemConfigService::class)]);

    $services->set(MD5ThemePathBuilder::class);

    $services->set(CompileThemeHandler::class)
        ->args([
            service(ThemeCompiler::class),
            service(AbstractConfigLoader::class),
            service(StorefrontPluginRegistry::class),
            service(NotificationService::class),
            service('sales_channel.repository'),
            service(ThemeRuntimeConfigService::class),
        ])
        ->tag('messenger.message_handler');

    $services->set(DeleteThemeFilesTask::class)
        ->tag('shopwell.scheduled.task');

    $services->set(DeleteThemeFilesTaskHandler::class)
        ->args([
            service('scheduled_task.repository'),
            service('logger'),
            service(Connection::class),
            service('shopwell.filesystem.theme'),
            service(AbstractThemePathBuilder::class),
        ])
        ->tag('messenger.message_handler');

    $services->set(StaticFileConfigLoader::class)
        ->args([service('shopwell.filesystem.private')]);

    $services->set(StaticFileAvailableThemeProvider::class)
        ->args([service('shopwell.filesystem.private')]);

    $services->set(StaticFileConfigDumper::class)
        ->args([
            service(DatabaseConfigLoader::class),
            service(DatabaseAvailableThemeProvider::class),
            service('shopwell.filesystem.private'),
            service('shopwell.filesystem.temp'),
        ])
        ->tag('kernel.event_subscriber');

    $services->set('shopwell.asset.theme', ThemeAssetPackage::class)
        ->lazy()
        ->args([
            ['%shopwell.filesystem.theme.url%'],
            service('shopwell.asset.theme.version_strategy'),
            service('request_stack'),
            service(AbstractThemePathBuilder::class),
        ])
        ->tag('shopwell.asset', ['asset' => 'theme']);

    $services->set(ThemeDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(ThemeTranslationDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(ThemeSalesChannelDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(ThemeMediaDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(ThemeChildDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(SalesChannelExtension::class)
        ->tag('shopwell.entity.extension');

    $services->set(LanguageExtension::class)
        ->tag('shopwell.entity.extension');

    $services->set(MediaExtension::class)
        ->tag('shopwell.entity.extension');

    $services->set(ThemeController::class)
        ->public()
        ->args([
            service(ThemeService::class),
            service(ScssPhpCompiler::class),
            '%storefront.theme.allowed_scss_values%',
        ])
        ->call('setContainer', [service('service_container')]);

    $services->set(ThemeCreateCommand::class)
        ->args([
            '%kernel.project_dir%',
            service(Filesystem::class),
        ])
        ->tag('console.command');

    $services->set(ThemeChangeCommand::class)
        ->args([
            service(ThemeService::class),
            service(StorefrontPluginRegistry::class),
            service('sales_channel.repository'),
            service('theme.repository'),
            service('theme_sales_channel.repository'),
            service('media_thumbnail.repository'),
        ])
        ->tag('console.command');

    $services->set(ThemeCompileCommand::class)
        ->args([
            service(ThemeService::class),
            service(AbstractAvailableThemeProvider::class),
        ])
        ->tag('console.command');

    $services->set(ThemeDumpCommand::class)
        ->args([
            service(StorefrontPluginRegistry::class),
            service(ThemeFileResolver::class),
            service('theme.repository'),
            service(StaticFileConfigDumper::class),
            service(ThemeFilesystemResolver::class),
        ])
        ->tag('console.command');

    $services->set(ThemeRefreshCommand::class)
        ->args([service(ThemeLifecycleService::class)])
        ->tag('console.command');

    $services->set(ThemePrepareIconsCommand::class)
        ->tag('console.command');

    $services->set(PluginLifecycleSubscriber::class)
        ->args([
            service(StorefrontPluginRegistry::class),
            '%kernel.project_dir%',
            service(StorefrontPluginConfigurationFactory::class),
            service(ThemeLifecycleHandler::class),
            service(ThemeLifecycleService::class),
        ])
        ->tag('kernel.event_subscriber');

    $services->set(ThemeInheritanceBuilderInterface::class, ThemeInheritanceBuilder::class)
        ->args([service(ThemeRuntimeConfigService::class)]);

    $services->set(AppLifecycleSubscriber::class)
        ->args([
            service(ThemeLifecycleService::class),
            service('app.repository'),
        ])
        ->tag('kernel.event_subscriber');

    $services->set(ThemeCompilerEnrichScssVarSubscriber::class)
        ->args([
            service(ConfigurationService::class),
            service(StorefrontPluginRegistry::class),
        ])
        ->tag('kernel.event_subscriber');

    $services->set(ThemeNamespaceHierarchyBuilder::class)
        ->args([
            service(ThemeInheritanceBuilderInterface::class),
            service(DatabaseSalesChannelThemeLoader::class),
        ])
        ->tag('kernel.event_subscriber')
        ->tag('kernel.reset', ['method' => 'reset'])
        ->tag('shopwell.twig.hierarchy_builder', ['priority' => 500]);

    $services->set(FirstRunWizardSubscriber::class)
        ->args([
            service(ThemeService::class),
            service(ThemeLifecycleService::class),
            service('theme.repository'),
            service('theme_sales_channel.repository'),
            service('sales_channel.repository'),
        ])
        ->tag('kernel.event_subscriber');

    $services->set(UpdateSubscriber::class)
        ->args([
            service(ThemeService::class),
            service(ThemeLifecycleService::class),
            service('sales_channel.repository'),
        ])
        ->tag('kernel.event_subscriber');

    $services->set(UnusedMediaSubscriber::class)
        ->args([
            service('theme.repository'),
            service(ThemeService::class),
        ])
        ->tag('kernel.event_subscriber');

    $services->set(ThemeIndexer::class)
        ->args([
            service(IteratorFactory::class),
            service('theme.repository'),
            service(Connection::class),
            service('event_dispatcher'),
        ])
        ->tag('shopwell.entity_indexer');

    $services->set(DatabaseSalesChannelThemeLoader::class)
        ->public()
        ->args([service(Connection::class)]);

    $services->set(ThemeFilesystemResolver::class)
        ->public()
        ->args([
            service(SourceResolver::class),
            service('kernel'),
        ]);

    $services->set(ThemeSnippetsSubscriber::class)
        ->args([
            service(ThemeRuntimeConfigService::class),
            service(DatabaseSalesChannelThemeLoader::class),
        ])
        ->tag('kernel.event_subscriber');
};
