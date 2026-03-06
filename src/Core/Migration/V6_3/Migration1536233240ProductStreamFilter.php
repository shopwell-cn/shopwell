<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1536233240ProductStreamFilter extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536233240;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `product_stream_filter` (
              `id` binary(16) NOT NULL,
              `product_stream_id` binary(16) NOT NULL,
              `parent_id` binary(16) DEFAULT NULL,
              `type` varchar(255) NOT NULL,
              `field` varchar(255) DEFAULT NULL,
              `operator` varchar(255) DEFAULT NULL,
              `value` longtext DEFAULT NULL,
              `parameters` longtext DEFAULT NULL,
              `position` int(11) NOT NULL DEFAULT 0,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `fk.product_stream_filter.product_stream_id` (`product_stream_id`),
              KEY `fk.product_stream_filter.parent_id` (`parent_id`),
              CONSTRAINT `fk.product_stream_filter.parent_id` FOREIGN KEY (`parent_id`) REFERENCES `product_stream_filter` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.product_stream_filter.product_stream_id` FOREIGN KEY (`product_stream_id`) REFERENCES `product_stream` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.product_stream_filter.parameters` CHECK (json_valid(`parameters`)),
              CONSTRAINT `json.product_stream_filter.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
