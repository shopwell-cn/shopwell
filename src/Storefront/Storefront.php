<?php declare(strict_types=1);

namespace Shopwell\Storefront;

use Shopwell\Core\Framework\Bundle;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Storefront\DependencyInjection\DisableTemplateCachePass;
use Shopwell\Storefront\DependencyInjection\StorefrontMigrationReplacementCompilerPass;
use Shopwell\Storefront\Framework\ThemeInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/**
 * @internal
 */
#[Package('framework')]
class Storefront extends Bundle implements ThemeInterface
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $this->buildDefaultConfig($container);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/DependencyInjection'));
        $loader->load('services.php');
        $loader->load('captcha.php');
        $loader->load('seo.php');
        $loader->load('controller.php');
        $loader->load('theme.php');
        $loader->load('system.php');

        $container->setParameter('storefrontRoot', $this->getPath());

        $container->addCompilerPass(new DisableTemplateCachePass());
        $container->addCompilerPass(new StorefrontMigrationReplacementCompilerPass());
    }
}
