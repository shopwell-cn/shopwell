<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1595492052SeoUrl extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1595492052;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<'SQL'
            CREATE TABLE `seo_url` (
              `id` binary(16) NOT NULL,
              `language_id` binary(16) NOT NULL,
              `sales_channel_id` binary(16) DEFAULT NULL,
              `foreign_key` binary(16) NOT NULL,
              `route_name` varchar(50) NOT NULL,
              `path_info` varchar(750) NOT NULL,
              `seo_path_info` varchar(750) NOT NULL,
              `is_canonical` tinyint(1) DEFAULT NULL,
              `is_modified` tinyint(1) NOT NULL DEFAULT 0,
              `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq.seo_url.seo_path_info` (`language_id`,`sales_channel_id`,`seo_path_info`),
              UNIQUE KEY `uniq.seo_url.foreign_key` (`language_id`,`sales_channel_id`,`foreign_key`,`route_name`,`is_canonical`),
              KEY `fk.seo_url.sales_channel_id` (`sales_channel_id`),
              KEY `idx.foreign_key` (`language_id`,`foreign_key`,`sales_channel_id`,`is_canonical`),
              KEY `idx.path_info` (`language_id`,`sales_channel_id`,`is_canonical`,`path_info`),
              KEY `idx.delete_query` (`foreign_key`,`sales_channel_id`),
              CONSTRAINT `fk.seo_url.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.seo_url.sales_channel_id` FOREIGN KEY (`sales_channel_id`) REFERENCES `sales_channel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.seo_url.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
