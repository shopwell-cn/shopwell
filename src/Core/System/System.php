<?php declare(strict_types=1);

namespace Shopwell\Core\System;

use Shopwell\Core\Framework\Bundle;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\System\CustomEntity\CustomEntityRegistrar;
use Shopwell\Core\System\DependencyInjection\CompilerPass\NumberRangeIncrementerCompilerPass;
use Shopwell\Core\System\DependencyInjection\CompilerPass\SalesChannelEntityCompilerPass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/**
 * @internal
 */
#[Package('framework')]
class System extends Bundle
{
    public function getTemplatePriority(): int
    {
        return -1;
    }

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/DependencyInjection/'));
        $loader->load('sales_channel.php');
        $loader->load('country.php');
        $loader->load('currency.php');
        $loader->load('custom_entity.php');
        $loader->load('snippet.php');
        $loader->load('user.php');
        $loader->load('integration.php');
        $loader->load('state_machine.php');
        $loader->load('configuration.php');
        $loader->load('number_range.php');
        $loader->load('consent.php');
        $loader->load('data_dict.php');
        $loader->load('tax.php');
        $loader->load('tax_provider.php');
        $loader->load('unit.php');
        $loader->load('tag.php');
        $loader->load('locale.php');

        $container->addCompilerPass(new SalesChannelEntityCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 0);
        $container->addCompilerPass(new NumberRangeIncrementerCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 0);
    }

    public function boot(): void
    {
        parent::boot();

        \assert($this->container instanceof ContainerInterface, 'Container is not set yet, please call setContainer() before calling boot(), see `src/Core/Kernel.php:186`.');

        $this->container->get(CustomEntityRegistrar::class)->register();
    }
}
