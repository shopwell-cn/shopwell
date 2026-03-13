<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\App\Lifecycle\Persister;

use Shopwell\Core\Framework\App\Lifecycle\AppLifecycleContext;
use Shopwell\Core\Framework\Log\Package;

/**
 * @internal only for use by the app-system
 */
#[Package('framework')]
interface PersisterInterface
{
    public function persist(AppLifecycleContext $context): void;
}
