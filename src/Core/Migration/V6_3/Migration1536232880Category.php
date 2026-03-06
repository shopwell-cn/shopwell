<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1536232880Category extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536232880;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `category` (
              `id` binary(16) NOT NULL,
              `version_id` binary(16) NOT NULL,
              `auto_increment` int(11) NOT NULL AUTO_INCREMENT,
              `parent_id` binary(16) DEFAULT NULL,
              `parent_version_id` binary(16) DEFAULT NULL,
              `media_id` binary(16) DEFAULT NULL,
              `product_assignment_type` varchar(32) NOT NULL DEFAULT \'product\',
              `path` longtext DEFAULT NULL,
              `after_category_id` binary(16) DEFAULT NULL,
              `after_category_version_id` binary(16) DEFAULT NULL,
              `level` int(11) unsigned NOT NULL DEFAULT 1,
              `active` tinyint(1) NOT NULL DEFAULT 1,
              `child_count` int(11) unsigned NOT NULL DEFAULT 0,
              `display_nested_products` tinyint(1) unsigned NOT NULL DEFAULT 1,
              `visible` tinyint(1) unsigned NOT NULL DEFAULT 1,
              `type` varchar(32) NOT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`,`version_id`),
              UNIQUE KEY `auto_increment` (`auto_increment`),
              KEY `idx.level` (`level`),
              KEY `fk.category.media_id` (`media_id`),
              KEY `fk.category.parent_id` (`parent_id`,`parent_version_id`),
              KEY `fk.category.after_category_id` (`after_category_id`,`after_category_version_id`),
              CONSTRAINT `fk.category.after_category_id` FOREIGN KEY (`after_category_id`, `after_category_version_id`) REFERENCES `category` (`id`, `version_id`) ON DELETE SET NULL ON UPDATE CASCADE,
              CONSTRAINT `fk.category.media_id` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
              CONSTRAINT `fk.category.parent_id` FOREIGN KEY (`parent_id`, `parent_version_id`) REFERENCES `category` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `category_translation` (
              `category_id` binary(16) NOT NULL,
              `category_version_id` binary(16) NOT NULL,
              `language_id` binary(16) NOT NULL,
              `name` varchar(255) DEFAULT NULL,
              `breadcrumb` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`breadcrumb`)),
              `internal_link` binary(16) DEFAULT NULL,
              `link_new_tab` tinyint(4) DEFAULT NULL,
              `link_type` varchar(255) DEFAULT NULL,
              `external_link` mediumtext DEFAULT NULL,
              `description` longtext DEFAULT NULL,
              `meta_title` varchar(255) DEFAULT NULL,
              `meta_description` varchar(255) DEFAULT NULL,
              `keywords` varchar(255) DEFAULT NULL,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `slot_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`slot_config`)),
              PRIMARY KEY (`category_id`,`category_version_id`,`language_id`),
              KEY `fk.category_translation.language_id` (`language_id`),
              CONSTRAINT `fk.category_translation.category_id` FOREIGN KEY (`category_id`, `category_version_id`) REFERENCES `category` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.category_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.category_translation.custom_fields` CHECK (json_valid(`custom_fields`)),
              CONSTRAINT `json.category_translation.slot_config` CHECK (json_valid(`slot_config`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
