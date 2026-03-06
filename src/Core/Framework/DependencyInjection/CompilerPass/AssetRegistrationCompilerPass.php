<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\DependencyInjection\CompilerPass;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Storefront\Theme\ThemeCompiler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

#[Package('framework')]
class AssetRegistrationCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $assets = [];
        foreach ($container->findTaggedServiceIds('shopwell.asset') as $id => $config) {
            $container->getDefinition($id)->addTag('assets.package', ['package' => $config[0]['asset']]);
            $assets[$config[0]['asset']] = new Reference($id);
        }

        $assetService = $container->getDefinition('assets.packages');
        $assetService->addMethodCall('setDefaultPackage', [$assets['asset']]);

        /** @phpstan-ignore phpat.restrictNamespacesInCore (Existence of Storefront dependency is checked before usage. Don't do that! Will be fixed with https://github.com/shopwell/shopwell/issues/12966) */
        if ($container->hasDefinition(ThemeCompiler::class)) {
            /** @phpstan-ignore phpat.restrictNamespacesInCore */
            $container->getDefinition(ThemeCompiler::class)->replaceArgument(7, $assets);
        }
    }
}
