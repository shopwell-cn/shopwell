<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1536232710Integration extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536232710;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `integration` (
              `id` binary(16) NOT NULL,
              `access_key` varchar(255) NOT NULL,
              `secret_access_key` varchar(255) NOT NULL,
              `label` varchar(255) NOT NULL,
              `admin` tinyint(1) NOT NULL DEFAULT 1,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `last_usage_at` datetime(3) DEFAULT NULL,
              `deleted_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `idx.access_key` (`access_key`),
              CONSTRAINT `json.integration.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
