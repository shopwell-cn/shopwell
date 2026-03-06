<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_4;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1635237551Script extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1635237551;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `script` (
              `id` binary(16) NOT NULL,
              `script` longtext NOT NULL,
              `hook` varchar(255) NOT NULL,
              `name` varchar(1024) NOT NULL,
              `active` tinyint(1) NOT NULL,
              `app_id` binary(16) DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `idx.app_script.hook` (`hook`),
              KEY `fk.app_script.app_id` (`app_id`),
              CONSTRAINT `fk.app_script.app_id` FOREIGN KEY (`app_id`) REFERENCES `app` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // nth
    }
}
