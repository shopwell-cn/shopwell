<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
#[Package('framework')]
class Migration1536232620DataDict extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536232610;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement(
            'CREATE TABLE `data_dict_group` (
               `id`          BINARY(16)                              NOT NULL,
               `code`        VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
               `active` tinyint(1) NOT NULL DEFAULT \'1\',
               `position` int NOT NULL DEFAULT \'1\',
               `created_at`  DATETIME(3)                             NOT NULL,
               `updated_at`  DATETIME(3)                             NULL,
               PRIMARY KEY (`id`),
               UNIQUE KEY `uniq.code` (`code`)
             ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
        );

        $connection->executeStatement('
            CREATE TABLE `data_dict_group_translation` (
              `data_dict_group_id` binary(16) NOT NULL,
              `language_id` binary(16) NOT NULL,
              `name` varchar(255) DEFAULT NULL,
              `description` longtext DEFAULT NULL,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`data_dict_group_id`,`language_id`),
              KEY `fk.data_dict_group_translation.language_id` (`language_id`),
              CONSTRAINT `fk.data_dict_group_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.data_dict_group_translation.data_dict_group_id` FOREIGN KEY (`data_dict_group_id`) REFERENCES `data_dict_group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.data_dict_group_translation.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement(
            'CREATE TABLE `data_dict_item` (
               `id`          BINARY(16)                              NOT NULL,
               `group_id`          BINARY(16)                              NOT NULL,
               `option_value` varchar(255) NOT NULL ,
               `parent_id` binary(16) DEFAULT NULL,
               `level` int unsigned NOT NULL DEFAULT \'1\',
               `child_count` int unsigned NOT NULL DEFAULT \'0\',
               `path` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
               `active` tinyint(1) NOT NULL DEFAULT \'1\',
               `position` int NOT NULL DEFAULT \'1\',
               `created_at`  DATETIME(3)                             NOT NULL,
               `updated_at`  DATETIME(3)                             NULL,
               PRIMARY KEY (`id`),
               KEY `fk.data_dict_item.parent_id` (`parent_id`),
               KEY `fk.data_dict_item.group_id` (`group_id`),
               UNIQUE `uniq.data_dict_item.option_value` (`group_id`, `option_value`),
               CONSTRAINT `fk.data_dict_item.group_id` FOREIGN KEY (`group_id`) REFERENCES `data_dict_group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
               CONSTRAINT `fk.data_dict_item.parent_id` FOREIGN KEY (`parent_id`) REFERENCES `data_dict_item` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
             ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
        );

        $connection->executeStatement('
            CREATE TABLE `data_dict_item_translation` (
              `data_dict_item_id` binary(16) NOT NULL,
              `language_id` binary(16) NOT NULL,
              `name` varchar(255) DEFAULT NULL,
              `description` longtext DEFAULT NULL,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`data_dict_item_id`,`language_id`),
              KEY `fk.data_dict_item_translation.language_id` (`language_id`),
              CONSTRAINT `fk.data_dict_item_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.data_dict_item_translation.data_dict_item_id` FOREIGN KEY (`data_dict_item_id`) REFERENCES `data_dict_item` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.data_dict_item_translation.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
