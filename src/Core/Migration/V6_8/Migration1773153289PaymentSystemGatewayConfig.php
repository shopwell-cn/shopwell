<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_8;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1773153289PaymentSystemGatewayConfig extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1773153289;
    }

    public function update(Connection $connection): void
    {
    }
}
