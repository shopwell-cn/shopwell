<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout;

use Shopwell\Core\Checkout\DependencyInjection\CompilerPass\CartStorageCompilerPass;
use Shopwell\Core\Framework\Bundle;
use Shopwell\Core\Framework\Log\Package;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/**
 * @internal
 */
#[Package('checkout')]
class Checkout extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new CartStorageCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 0);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/DependencyInjection/'));
        $loader->load('cart.php');
        $loader->load('customer.php');
        $loader->load('document.php');
        $loader->load('order.php');
        $loader->load('payment.php');
        $loader->load('promotion.php');
        $loader->load('rule.php');
        $loader->load('shipping.php');
        $loader->load('wallet.php');
        $loader->load('wallet.php');
        $loader->load('affiliate.php');
        $loader->load('points.php');
    }
}
