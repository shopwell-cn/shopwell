<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem;

use Payum\Bundle\PayumBundle\PayumBundle;
use Shopwell\Core\Framework\Bundle;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Parameter\AdditionalBundleParameters;
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
        $phpLoader->load('services.php');

        parent::build($container);
        $this->buildDefaultConfig($container);
    }

    public function getAdditionalBundles(AdditionalBundleParameters $parameters): array
    {
        return [
            new PayumBundle(),
        ];
    }
}
