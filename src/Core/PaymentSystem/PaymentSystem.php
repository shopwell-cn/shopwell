<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem;

use Shopwell\Core\Framework\Bundle;
use Shopwell\Core\Framework\Log\Package;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/**
 * @internal
 */
#[Package('payment-system')]
class PaymentSystem extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        $configLocator = new FileLocator(__DIR__ . '/DependencyInjection/');

        $phpLoader = new PhpFileLoader($container, $configLocator);
        $phpLoader->load('gateway.php');
        $phpLoader->load('order.php');
        $phpLoader->load('api.php');
    }
}
