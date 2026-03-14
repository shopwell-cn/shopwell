<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1595492053SeoUrlTemplate extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1595492053;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `seo_url_template` (
              `id` binary(16) NOT NULL,
              `sales_channel_id` binary(16) DEFAULT NULL,
              `route_name` varchar(255) NOT NULL,
              `entity_name` varchar(64) NOT NULL,
              `template` varchar(750) DEFAULT NULL,
              `is_valid` tinyint(1) NOT NULL DEFAULT 1,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq.seo_url_template.route_name` (`sales_channel_id`,`route_name`),
              CONSTRAINT `fk.seo_url_template.sales_channel_id` FOREIGN KEY (`sales_channel_id`) REFERENCES `sales_channel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.seo_url_template.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
