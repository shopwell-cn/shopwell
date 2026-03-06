<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_6;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1733745893createTagStorageTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1733745893;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `invalidation_tags` (
              `id` binary(16) NOT NULL,
              `tag` varchar(255) NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `tag` (`tag`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }
}
