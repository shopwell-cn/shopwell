<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1536232690Version extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536232690;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `version` (
              `id` binary(16) NOT NULL,
              `name` varchar(255) NOT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `idx.version.created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `version_commit` (
              `id` binary(16) NOT NULL,
              `auto_increment` bigint(20) NOT NULL AUTO_INCREMENT,
              `is_merge` tinyint(1) NOT NULL DEFAULT 0,
              `message` varchar(5000) DEFAULT NULL,
              `user_id` binary(16) DEFAULT NULL,
              `integration_id` binary(16) DEFAULT NULL,
              `version_id` binary(16) NOT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `auto_increment` (`auto_increment`),
              KEY `idx.version_commit.created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `version_commit_data` (
              `id` binary(16) NOT NULL,
              `auto_increment` bigint(20) NOT NULL AUTO_INCREMENT,
              `version_commit_id` binary(16) NOT NULL,
              `entity_name` varchar(100) NOT NULL,
              `entity_id` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`entity_id`)),
              `action` varchar(100) NOT NULL,
              `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`payload`)),
              `user_id` binary(16) DEFAULT NULL,
              `integration_id` binary(16) DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `auto_increment` (`auto_increment`),
              KEY `fk.version_commit_data.version_commit_id` (`version_commit_id`),
              CONSTRAINT `fk.version_commit_data.version_commit_id` FOREIGN KEY (`version_commit_id`) REFERENCES `version_commit` (`id`) ON DELETE CASCADE,
              CONSTRAINT `json.version_commit_data.entity_id` CHECK (json_valid(`entity_id`)),
              CONSTRAINT `json.version_commit_data.payload` CHECK (json_valid(`payload`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
