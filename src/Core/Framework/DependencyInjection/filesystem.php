<?php declare(strict_types=1);

use League\Flysystem\FilesystemOperator;
use Shopwell\Core\Framework\Adapter\Asset\AssetInstallCommand;
use Shopwell\Core\Framework\Adapter\Asset\FallbackUrlPackage;
use Shopwell\Core\Framework\Adapter\Asset\FlysystemLastModifiedVersionStrategy;
use Shopwell\Core\Framework\Adapter\Filesystem\Adapter\AwsS3v3Factory;
use Shopwell\Core\Framework\Adapter\Filesystem\Adapter\GoogleStorageFactory;
use Shopwell\Core\Framework\Adapter\Filesystem\Adapter\LocalFactory;
use Shopwell\Core\Framework\Adapter\Filesystem\FilesystemFactory;
use Shopwell\Core\Framework\Adapter\Filesystem\Plugin\CopyBatchInputFactory;
use Shopwell\Core\Framework\App\ActiveAppsLoader;
use Shopwell\Core\Framework\Plugin\Util\AssetService;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(FilesystemFactory::class)
        ->args([tagged_iterator('shopwell.filesystem.factory')]);

    $services->set('shopwell.filesystem.public', FilesystemOperator::class)
        ->public()
        ->args(['%shopwell.filesystem.public%'])
        ->factory([service(FilesystemFactory::class), 'factory']);

    $services->set('shopwell.filesystem.private', FilesystemOperator::class)
        ->public()
        ->args(['%shopwell.filesystem.private%'])
        ->factory([service(FilesystemFactory::class), 'privateFactory']);

    $services->set('shopwell.filesystem.temp', FilesystemOperator::class)
        ->public()
        ->args(['%shopwell.filesystem.temp%'])
        ->factory([service(FilesystemFactory::class), 'privateFactory']);

    $services->set('shopwell.filesystem.theme', FilesystemOperator::class)
        ->public()
        ->args(['%shopwell.filesystem.theme%'])
        ->factory([service(FilesystemFactory::class), 'factory']);

    $services->set('shopwell.filesystem.sitemap', FilesystemOperator::class)
        ->public()
        ->args(['%shopwell.filesystem.sitemap%'])
        ->factory([service(FilesystemFactory::class), 'factory']);

    $services->set('shopwell.filesystem.asset', FilesystemOperator::class)
        ->public()
        ->args(['%shopwell.filesystem.asset%'])
        ->factory([service(FilesystemFactory::class), 'factory']);

    $services->set('Shopwell\Core\Framework\Adapter\Filesystem\FilesystemFactory.local', LocalFactory::class)
        ->tag('shopwell.filesystem.factory');

    $services->set('Shopwell\Core\Framework\Adapter\Filesystem\FilesystemFactory.amazon_s3', AwsS3v3Factory::class)
        ->args(['%shopwell.filesystem.batch_write_size%'])
        ->tag('shopwell.filesystem.factory');

    $services->set('Shopwell\Core\Framework\Adapter\Filesystem\FilesystemFactory.google_storage', GoogleStorageFactory::class)
        ->tag('shopwell.filesystem.factory');

    $services->set('console.command.assets_install', AssetInstallCommand::class)
        ->args([
            service('kernel'),
            service(AssetService::class),
            service(ActiveAppsLoader::class),
        ])
        ->tag('console.command');

    $services->set('shopwell.asset.public', FallbackUrlPackage::class)
        ->lazy()
        ->args([
            ['%shopwell.filesystem.public.url%'],
            service('assets.empty_version_strategy'),
            service('request_stack')->nullOnInvalid(),
        ])
        ->tag('shopwell.asset', ['asset' => 'public']);

    $services->set('shopwell.asset.public.version_strategy', FlysystemLastModifiedVersionStrategy::class)
        ->args([
            'theme-metaData',
            service('shopwell.filesystem.public'),
            service('cache.object'),
        ]);

    $services->set('shopwell.asset.theme.version_strategy', FlysystemLastModifiedVersionStrategy::class)
        ->args([
            'theme-metaData',
            service('shopwell.filesystem.theme'),
            service('cache.object'),
        ]);

    $services->set('shopwell.asset.asset.version_strategy', FlysystemLastModifiedVersionStrategy::class)
        ->args([
            'asset-metaData',
            service('shopwell.filesystem.asset'),
            service('cache.object'),
        ]);

    $services->set('shopwell.asset.asset', FallbackUrlPackage::class)
        ->lazy()
        ->args([
            ['%shopwell.filesystem.asset.url%'],
            service('shopwell.asset.asset.version_strategy'),
            service('request_stack')->nullOnInvalid(),
        ])
        ->tag('shopwell.asset', ['asset' => 'asset']);

    $services->set('shopwell.asset.asset_without_versioning', FallbackUrlPackage::class)
        ->lazy()
        ->args([
            ['%shopwell.filesystem.asset.url%'],
            service('assets.empty_version_strategy'),
            service('request_stack')->nullOnInvalid(),
        ]);

    $services->set('shopwell.asset.sitemap', FallbackUrlPackage::class)
        ->lazy()
        ->args([
            ['%shopwell.filesystem.sitemap.url%'],
            service('assets.empty_version_strategy'),
            service('request_stack')->nullOnInvalid(),
        ])
        ->tag('shopwell.asset', ['asset' => 'sitemap']);

    $services->set(CopyBatchInputFactory::class);
};
