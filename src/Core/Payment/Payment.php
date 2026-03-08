<?php declare(strict_types=1);

namespace Shopwell\Core\Payment;

use Payum\Bundle\PayumBundle\PayumBundle;
use Shopwell\Core\Framework\Bundle;
use Shopwell\Core\Framework\Parameter\AdditionalBundleParameters;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/**
 * @internal
 */
class Payment extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $this->buildDefaultConfig($container);

        $phpLoader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/DependencyInjection/'));
        $phpLoader->load('payum.php');
    }

    public function getAdditionalBundles(AdditionalBundleParameters $parameters): array
    {
        return [
            new PayumBundle(),
        ];
    }
}
