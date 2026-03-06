<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1536233340NumberRange extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536233340;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<'SQL'
            CREATE TABLE `number_range` (
              `id` binary(16) NOT NULL,
              `type_id` binary(16) NOT NULL,
              `global` tinyint(1) NOT NULL,
              `pattern` varchar(255) NOT NULL,
              `start` int(8) NOT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<'SQL'
            CREATE TABLE `number_range_translation` (
              `number_range_id` binary(16) NOT NULL,
              `name` varchar(64) DEFAULT NULL,
              `description` varchar(255) DEFAULT NULL,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `language_id` binary(16) NOT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`number_range_id`,`language_id`),
              KEY `fk.number_range_translation.language_id` (`language_id`),
              CONSTRAINT `fk.number_range_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.number_range_translation.number_range_id` FOREIGN KEY (`number_range_id`) REFERENCES `number_range` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.number_range_translation.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<'SQL'
            CREATE TABLE `number_range_state` (
              `id` binary(16) NOT NULL,
              `number_range_id` binary(16) NOT NULL,
              `last_value` int(8) NOT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`number_range_id`),
              UNIQUE KEY `uniq.id` (`id`),
              KEY `idx.number_range_id` (`number_range_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        // No Foreign Key here is intended. It should be possible to handle the state with another Persistence so
        // we can force MySQL to expect a Dependency here
        $connection->executeStatement($sql);

        $sql = <<<'SQL'
            CREATE TABLE `number_range_type` (
              `id` binary(16) NOT NULL,
              `technical_name` varchar(64) DEFAULT NULL,
              `global` tinyint(1) NOT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq.technical_name` (`technical_name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($sql);

        $sql = <<<'SQL'
            CREATE TABLE `number_range_type_translation` (
              `number_range_type_id` binary(16) NOT NULL,
              `language_id` binary(16) NOT NULL,
              `type_name` varchar(64) DEFAULT NULL,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`number_range_type_id`,`language_id`),
              KEY `fk.number_range_type_translation.language_id` (`language_id`),
              CONSTRAINT `fk.number_range_type_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.number_range_type_translation.number_range_type_id` FOREIGN KEY (`number_range_type_id`) REFERENCES `number_range_type` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.number_range_type_translation.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($sql);

        $sql = <<<'SQL'
            CREATE TABLE `number_range_sales_channel` (
              `id` binary(16) NOT NULL,
              `number_range_id` binary(16) NOT NULL,
              `sales_channel_id` binary(16) DEFAULT NULL,
              `number_range_type_id` binary(16) NOT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq.numer_range_id__sales_channel_id` (`number_range_id`,`sales_channel_id`),
              KEY `fk.number_range_sales_channel.sales_channel_id` (`sales_channel_id`),
              KEY `fk.number_range_sales_channel.number_range_type_id` (`number_range_type_id`),
              CONSTRAINT `fk.number_range_sales_channel.number_range_id` FOREIGN KEY (`number_range_id`) REFERENCES `number_range` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.number_range_sales_channel.number_range_type_id` FOREIGN KEY (`number_range_type_id`) REFERENCES `number_range_type` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.number_range_sales_channel.sales_channel_id` FOREIGN KEY (`sales_channel_id`) REFERENCES `sales_channel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
