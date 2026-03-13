<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1536232720Country extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536232720;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `country` (
              `id` binary(16) NOT NULL,
              `iso` varchar(255) DEFAULT NULL,
              `position` int(11) NOT NULL DEFAULT 1,
              `active` tinyint(1) NOT NULL DEFAULT 1,
              `iso3` varchar(45) DEFAULT NULL,
              `is_eu` tinyint(1) NOT NULL DEFAULT 0,
              `display_state_in_registration` tinyint(1) NOT NULL DEFAULT 0,
              `force_state_in_registration` tinyint(1) NOT NULL DEFAULT 0,
              `check_vat_id_pattern` tinyint(1) NOT NULL DEFAULT 0,
              `company_tax` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`company_tax`)),
              `vat_id_pattern` varchar(255) DEFAULT NULL,
              `vat_id_required` tinyint(1) NOT NULL DEFAULT 0,
              `customer_tax` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`customer_tax`)),
              `advanced_postal_code_pattern` varchar(255) DEFAULT NULL,
              `check_advanced_postal_code_pattern` tinyint(1) NOT NULL DEFAULT 0,
              `check_postal_code_pattern` tinyint(1) NOT NULL DEFAULT 0,
              `default_postal_code_pattern` varchar(1024) DEFAULT NULL,
              `postal_code_required` tinyint(1) NOT NULL DEFAULT 0,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `shipping_available` tinyint(1) NOT NULL DEFAULT 1,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `country_translation` (
              `country_id` binary(16) NOT NULL,
              `language_id` binary(16) NOT NULL,
              `name` varchar(255) DEFAULT NULL,
              `address_format` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`address_format`)),
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`country_id`,`language_id`),
              KEY `fk.country_translation.language_id` (`language_id`),
              CONSTRAINT `fk.country_translation.country_id` FOREIGN KEY (`country_id`) REFERENCES `country` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.country_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.country_translation.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `country_state` (
              `id`          BINARY(16)                              NOT NULL,
              `country_id`  BINARY(16)                              NOT NULL,
              `short_code`  VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `position`    INT(11)                                 NOT NULL DEFAULT 1,
              `active`      TINYINT(1)                              NOT NULL DEFAULT 1,
              `created_at`  DATETIME(3)                             NOT NULL,
              `updated_at`  DATETIME(3)                             NULL,
              PRIMARY KEY (`id`),
              CONSTRAINT `fk.country_state.country_id` FOREIGN KEY (`country_id`)
                REFERENCES `country` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `country_state_translation` (
              `country_state_id`    BINARY(16)                              NOT NULL,
              `language_id`         BINARY(16)                              NOT NULL,
              `name`                VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
              `custom_fields`       JSON                                    NULL,
              `created_at`          DATETIME(3)                             NOT NULL,
              `updated_at`          DATETIME(3)                             NULL,
              PRIMARY KEY (`country_state_id`, `language_id`),
              CONSTRAINT `json.country_state_translation.custom_fields` CHECK (JSON_VALID(`custom_fields`)),
              CONSTRAINT `fk.country_state_translation.language_id` FOREIGN KEY (`language_id`)
                REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.country_state_translation.country_state_id` FOREIGN KEY (`country_state_id`)
                REFERENCES `country_state` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `country_state_region` (
              `id` binary(16) NOT NULL,
              `state_id` binary(16) NOT NULL,
              `parent_id` binary(16) DEFAULT NULL,
              `short_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
              `path` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
              `level` int unsigned NOT NULL DEFAULT \'1\',
              `child_count` int unsigned NOT NULL DEFAULT \'0\',
              `position` int NOT NULL DEFAULT \'1\',
              `active` tinyint(1) NOT NULL DEFAULT \'1\',
              `lng` decimal(10,6)  NULL,
              `lat` decimal(10,6)  NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `fk.country_state_region.parent_id` (`parent_id`),
              KEY `fk.country_state_region.state_id` (`state_id`),
              CONSTRAINT `fk.country_state_region.parent_id` FOREIGN KEY (`parent_id`) REFERENCES `country_state_region` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.country_state_region.state_id` FOREIGN KEY (`state_id`) REFERENCES `country_state` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `country_state_region_translation` (
              `country_state_region_id` binary(16) NOT NULL,
              `language_id` binary(16) NOT NULL,
              `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `custom_fields` json DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`country_state_region_id`,`language_id`),
              KEY `fk.country_state_region_translation.language_id` (`language_id`),
              CONSTRAINT `fk.country_state_region_translation.country_state_region_id` FOREIGN KEY (`country_state_region_id`) REFERENCES `country_state_region` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.country_state_region_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.country_state_region_translation.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
