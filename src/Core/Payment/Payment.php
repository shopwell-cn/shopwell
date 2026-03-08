<?php declare(strict_types=1);

namespace Shopwell\Core\Payment;

use Shopwell\Core\Framework\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @internal
 */
class Payment extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $this->buildDefaultConfig($container);
    }
}
