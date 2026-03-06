<?php declare(strict_types=1);

namespace Shopwell\Core\Framework;

use Shopwell\Core\Framework\Adapter\Cache\CacheCompilerPass;
use Shopwell\Core\Framework\Adapter\Cache\CacheValueCompressor;
use Shopwell\Core\Framework\Adapter\Cache\ReverseProxy\ReverseProxyCompilerPass;
use Shopwell\Core\Framework\Adapter\Cache\StampedeProtectionConfigurator;
use Shopwell\Core\Framework\Adapter\Redis\RedisConnectionsCompilerPass;
use Shopwell\Core\Framework\DataAbstractionLayer\AttributeEntityCompiler;
use Shopwell\Core\Framework\DependencyInjection\CompilerPass\AssetBundleRegistrationCompilerPass;
use Shopwell\Core\Framework\DependencyInjection\CompilerPass\AssetRegistrationCompilerPass;
use Shopwell\Core\Framework\DependencyInjection\CompilerPass\AttributeEntityCompilerPass;
use Shopwell\Core\Framework\DependencyInjection\CompilerPass\AutoconfigureCompilerPass;
use Shopwell\Core\Framework\DependencyInjection\CompilerPass\CreateGeneratorScaffoldingCommandPass;
use Shopwell\Core\Framework\DependencyInjection\CompilerPass\DefaultTransportCompilerPass;
use Shopwell\Core\Framework\DependencyInjection\CompilerPass\DemodataCompilerPass;
use Shopwell\Core\Framework\DependencyInjection\CompilerPass\DisableTwigCacheWarmerCompilerPass;
use Shopwell\Core\Framework\DependencyInjection\CompilerPass\EntityCompilerPass;
use Shopwell\Core\Framework\DependencyInjection\CompilerPass\FeatureFlagCompilerPass;
use Shopwell\Core\Framework\DependencyInjection\CompilerPass\FilesystemConfigMigrationCompilerPass;
use Shopwell\Core\Framework\DependencyInjection\CompilerPass\FrameworkMigrationReplacementCompilerPass;
use Shopwell\Core\Framework\DependencyInjection\CompilerPass\HttpCacheConfigCompilerPass;
use Shopwell\Core\Framework\DependencyInjection\CompilerPass\MessengerMiddlewareCompilerPass;
use Shopwell\Core\Framework\DependencyInjection\CompilerPass\OverwriteSessionFactoryCompilerPass;
use Shopwell\Core\Framework\DependencyInjection\CompilerPass\RateLimiterCompilerPass;
use Shopwell\Core\Framework\DependencyInjection\CompilerPass\RedisPrefixCompilerPass;
use Shopwell\Core\Framework\DependencyInjection\CompilerPass\RouteScopeCompilerPass;
use Shopwell\Core\Framework\DependencyInjection\CompilerPass\TwigEnvironmentCompilerPass;
use Shopwell\Core\Framework\DependencyInjection\CompilerPass\TwigLoaderConfigCompilerPass;
use Shopwell\Core\Framework\DependencyInjection\FrameworkExtension;
use Shopwell\Core\Framework\Feature\FeatureFlagRegistry;
use Shopwell\Core\Framework\Increment\IncrementerGatewayCompilerPass;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\MessageQueue\MessageHandlerCompilerPass;
use Shopwell\Core\Framework\Telemetry\Metrics\MeterProvider;
use Shopwell\Core\Framework\Test\DependencyInjection\CompilerPass\ContainerVisibilityCompilerPass;
use Shopwell\Core\Framework\Test\RateLimiter\DisableRateLimiterCompilerPass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * @internal
 */
#[Package('framework')]
class Framework extends Bundle
{
    public function getTemplatePriority(): int
    {
        return -1;
    }

    public function getContainerExtension(): Extension
    {
        return new FrameworkExtension();
    }

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        $container->setParameter('locale', 'en-GB');

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/DependencyInjection/'));
        $loader->load('services.xml');
        $loader->load('acl.xml');
        $loader->load('cache.xml');
        $loader->load('api.xml');
        $loader->load('app.xml');
        $loader->load('custom-field.xml');
        $loader->load('data-abstraction-layer.xml');
        $loader->load('demodata.xml');
        $loader->load('event.xml');
        $loader->load('hydrator.xml');
        $loader->load('filesystem.xml');
        $loader->load('message-queue.xml');
        $loader->load('plugin.xml');
        $loader->load('rule.xml');
        $loader->load('scheduled-task.xml');
        $loader->load('store.xml');
        $loader->load('script.xml');
        $loader->load('language.xml');
        $loader->load('update.xml');
        $loader->load('seo.xml');
        $loader->load('webhook.xml');
        $loader->load('rate-limiter.xml');
        $loader->load('increment.xml');
        $loader->load('flag.xml');
        $loader->load('health.xml');
        $loader->load('telemetry.xml');
        $loader->load('notification.xml');
        $loader->load('sso.xml');

        if ($container->getParameter('kernel.environment') === 'test') {
            $loader->load('services_test.xml');
            $loader->load('store_test.xml');
            $loader->load('seo_test.xml');
            $loader->load('app_test.xml');
        }

        /** Needs to run after @see RegisterAutoconfigureAttributesPass (priority 100) to include all services that are autoconfigured */
        $container->addCompilerPass(new AttributeEntityCompilerPass(new AttributeEntityCompiler()), PassConfig::TYPE_BEFORE_OPTIMIZATION, 99);
        // make sure to remove services behind a feature flag, before some other compiler passes may reference them, therefore the high priority
        $container->addCompilerPass(new FeatureFlagCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 1000);
        $container->addCompilerPass(new EntityCompilerPass());
        $container->addCompilerPass(new DisableTwigCacheWarmerCompilerPass());
        $container->addCompilerPass(new DefaultTransportCompilerPass());
        $container->addCompilerPass(new MessengerMiddlewareCompilerPass());
        $container->addCompilerPass(new TwigLoaderConfigCompilerPass());
        $container->addCompilerPass(new TwigEnvironmentCompilerPass());
        $container->addCompilerPass(new RouteScopeCompilerPass());
        $container->addCompilerPass(new AssetRegistrationCompilerPass());
        $container->addCompilerPass(new AssetBundleRegistrationCompilerPass());
        $container->addCompilerPass(new FilesystemConfigMigrationCompilerPass());
        $container->addCompilerPass(new RateLimiterCompilerPass());
        $container->addCompilerPass(new IncrementerGatewayCompilerPass());
        $container->addCompilerPass(new ReverseProxyCompilerPass());
        $container->addCompilerPass(new CacheCompilerPass());
        $container->addCompilerPass(new OverwriteSessionFactoryCompilerPass());
        $container->addCompilerPass(new RedisPrefixCompilerPass(), PassConfig::TYPE_BEFORE_REMOVING, 0);
        $container->addCompilerPass(new AutoconfigureCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 1000);
        $container->addCompilerPass(new HttpCacheConfigCompilerPass());
        $container->addCompilerPass(new MessageHandlerCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 1000);
        $container->addCompilerPass(new CreateGeneratorScaffoldingCommandPass());
        $container->addCompilerPass(new RedisConnectionsCompilerPass());

        if ($container->getParameter('kernel.environment') === 'test') {
            $container->addCompilerPass(new DisableRateLimiterCompilerPass());
            $container->addCompilerPass(new ContainerVisibilityCompilerPass());
        }

        $container->addCompilerPass(new FrameworkMigrationReplacementCompilerPass());

        $container->addCompilerPass(new DemodataCompilerPass());

        parent::build($container);
        $this->buildDefaultConfig($container);
    }

    public function boot(): void
    {
        parent::boot();

        \assert($this->container instanceof ContainerInterface, 'Container is not set yet, please call setContainer() before calling boot(), see `src/Core/Kernel.php:186`.');

        /** @var FeatureFlagRegistry $featureFlagRegistry */
        $featureFlagRegistry = $this->container->get(FeatureFlagRegistry::class);
        $featureFlagRegistry->register();

        if ($this->container->getParameter('kernel.environment') !== 'test') {
            // Inject the meter early in the application lifecycle. This is needed to use the meter in special case (static contexts).
            MeterProvider::bindMeter($this->container);
        }

        CacheValueCompressor::$compress = $this->container->getParameter('shopwell.cache.cache_compression');
        CacheValueCompressor::$compressMethod = $this->container->getParameter('shopwell.cache.cache_compression_method');
        Feature::$emitDeprecations = $this->container->getParameter('kernel.debug');

        /** @var StampedeProtectionConfigurator $stampedeProtectionConfigurator */
        $stampedeProtectionConfigurator = $this->container->get(StampedeProtectionConfigurator::class);
        $stampedeProtectionConfigurator->apply();
    }
}
