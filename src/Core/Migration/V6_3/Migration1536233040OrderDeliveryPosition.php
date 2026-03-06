<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1536233040OrderDeliveryPosition extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536233040;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `order_delivery_position` (
              `id` binary(16) NOT NULL,
              `version_id` binary(16) NOT NULL,
              `order_delivery_id` binary(16) NOT NULL,
              `order_delivery_version_id` binary(16) NOT NULL,
              `order_line_item_id` binary(16) NOT NULL,
              `order_line_item_version_id` binary(16) NOT NULL,
              `price` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`price`)),
              `total_price` double GENERATED ALWAYS AS (json_unquote(json_extract(`price`,\'$.totalPrice\'))) VIRTUAL,
              `unit_price` double GENERATED ALWAYS AS (json_unquote(json_extract(`price`,\'$.unitPrice\'))) VIRTUAL,
              `quantity` int(11) GENERATED ALWAYS AS (json_unquote(json_extract(`price`,\'$.quantity\'))) VIRTUAL,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`,`version_id`),
              KEY `fk.order_delivery_position.order_delivery_id` (`order_delivery_id`,`order_delivery_version_id`),
              KEY `fk.order_delivery_position.order_line_item_id` (`order_line_item_id`,`order_line_item_version_id`),
              CONSTRAINT `fk.order_delivery_position.order_delivery_id` FOREIGN KEY (`order_delivery_id`, `order_delivery_version_id`) REFERENCES `order_delivery` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.order_delivery_position.order_line_item_id` FOREIGN KEY (`order_line_item_id`, `order_line_item_version_id`) REFERENCES `order_line_item` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.order_delivery_position.price` CHECK (json_valid(`price`)),
              CONSTRAINT `json.order_delivery_position.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
