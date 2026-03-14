<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1536232630PropertyGroup extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536232630;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `property_group` (
              `id` binary(16) NOT NULL,
              `sorting_type` varchar(50) NOT NULL DEFAULT \'alphanumeric\',
              `display_type` varchar(50) NOT NULL DEFAULT \'text\',
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `filterable` tinyint(1) NOT NULL DEFAULT 1,
              `visible_on_product_detail_page` tinyint(1) DEFAULT 1,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `property_group_translation` (
              `property_group_id` binary(16) NOT NULL,
              `language_id` binary(16) NOT NULL,
              `name` varchar(255) DEFAULT NULL,
              `description` longtext DEFAULT NULL,
              `position` int(11) DEFAULT 1,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`property_group_id`,`language_id`),
              KEY `fk.property_group_translation.language_id` (`language_id`),
              CONSTRAINT `fk.property_group_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.property_group_translation.property_group_id` FOREIGN KEY (`property_group_id`) REFERENCES `property_group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.property_group_translation.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
