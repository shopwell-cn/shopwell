<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1536233380UserRecovery extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536233380;
    }

    public function update(Connection $connection): void
    {
        $query = <<<'SQL'
            CREATE TABLE `user_recovery` (
              `id` binary(16) NOT NULL,
              `user_id` binary(16) NOT NULL,
              `hash` varchar(255) NOT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq.user_recovery.user_id` (`user_id`),
              CONSTRAINT `fk.user_recovery.user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($query);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
