<?php declare(strict_types=1);

namespace Shopwell\Core\Service;

use Shopwell\Core\Framework\Bundle;
use Shopwell\Core\Framework\Log\Package;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
#[Package('framework')]
class Service extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/DependencyInjection'));
        $loader->load('services.php');
        parent::build($container);
    }
}
