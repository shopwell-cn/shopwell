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
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

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
        $container->setParameter('locale', 'zh-CN');

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/DependencyInjection/'));
        $loader->load('services.php');
        $loader->load('acl.php');
        $loader->load('cache.php');
        $loader->load('api.php');
        $loader->load('app.php');
        $loader->load('custom-field.php');
        $loader->load('data-abstraction-layer.php');
        $loader->load('event.php');
        $loader->load('hydrator.php');
        $loader->load('filesystem.php');
        $loader->load('message-queue.php');
        $loader->load('plugin.php');
        $loader->load('rule.php');
        $loader->load('scheduled-task.php');
        $loader->load('store.php');
        $loader->load('script.php');
        $loader->load('language.php');
        $loader->load('update.php');
        $loader->load('seo.php');
        $loader->load('webhook.php');
        $loader->load('rate-limiter.php');
        $loader->load('increment.php');
        $loader->load('flag.php');
        $loader->load('health.php');
        $loader->load('telemetry.php');
        $loader->load('notification.php');
        $loader->load('sso.php');

        if ($container->getParameter('kernel.environment') === 'test') {
            $loader->load('services_test.php');
            $loader->load('store_test.php');
            $loader->load('seo_test.php');
            $loader->load('app_test.php');
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
