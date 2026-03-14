<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_4;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1659257496OrderLineItemDownload extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1659257496;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `order_line_item_download` (
              `id` binary(16) NOT NULL,
              `version_id` binary(16) NOT NULL,
              `position` int(11) NOT NULL DEFAULT 1,
              `access_granted` tinyint(1) NOT NULL DEFAULT 0,
              `order_line_item_id` binary(16) NOT NULL,
              `order_line_item_version_id` binary(16) NOT NULL,
              `media_id` binary(16) NOT NULL,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`,`version_id`),
              KEY `fk.order_line_item_download.media_id` (`media_id`),
              KEY `fk.order_line_item_download.order_line_item_id` (`order_line_item_id`,`order_line_item_version_id`),
              CONSTRAINT `fk.order_line_item_download.media_id` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `fk.order_line_item_download.order_line_item_id` FOREIGN KEY (`order_line_item_id`, `order_line_item_version_id`) REFERENCES `order_line_item` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.order_line_item_download.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }
}
