<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1536232810User extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536232810;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `user` (
              `id` binary(16) NOT NULL,
              `username` varchar(255) NOT NULL,
              `password` varchar(255) NOT NULL,
              `name` varchar(255) NOT NULL,
              `phone_number` varchar(255) NOT NULL,
              `title` varchar(255) DEFAULT NULL,
              `email` varchar(255) NOT NULL,
              `active` tinyint(1) NOT NULL DEFAULT 0,
              `admin` tinyint(1) DEFAULT NULL,
              `avatar_id` binary(16) DEFAULT NULL,
              `locale_id` binary(16) NOT NULL,
              `store_token` varchar(255) DEFAULT NULL,
              `last_updated_password_at` datetime(3) DEFAULT NULL,
              `time_zone` varchar(255) NOT NULL DEFAULT \'Asia/Shanghai\',
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq.user.email` (`email`),
              UNIQUE KEY `uniq.user.username` (`username`),
              KEY `fk.user.locale_id` (`locale_id`),
              KEY `fk.user.avatar_id` (`avatar_id`),
              CONSTRAINT `fk.user.locale_id` FOREIGN KEY (`locale_id`) REFERENCES `locale` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `json.user.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `user_access_key` (
              `id` binary(16) NOT NULL,
              `user_id` binary(16) NOT NULL,
              `access_key` varchar(255) NOT NULL,
              `secret_access_key` varchar(255) NOT NULL,
              `last_usage_at` datetime(3) DEFAULT NULL,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `idx.user_access_key.user_id_` (`user_id`),
              KEY `idx.user_access_key.access_key` (`access_key`),
              CONSTRAINT `fk.user_access_key.user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.user_access_key.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
