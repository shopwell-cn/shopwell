<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1536233020OrderDelivery extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536233020;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `order_delivery` (
              `id` binary(16) NOT NULL,
              `version_id` binary(16) NOT NULL,
              `order_id` binary(16) NOT NULL,
              `order_version_id` binary(16) NOT NULL,
              `state_id` binary(16) NOT NULL,
              `shipping_order_address_id` binary(16) DEFAULT NULL,
              `shipping_order_address_version_id` binary(16) DEFAULT NULL,
              `shipping_method_id` binary(16) NOT NULL,
              `tracking_codes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`tracking_codes`)),
              `shipping_date_earliest` date NOT NULL,
              `shipping_date_latest` date NOT NULL,
              `shipping_costs` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`shipping_costs`)),
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`,`version_id`),
              KEY `idx.state_index` (`state_id`),
              KEY `fk.order_delivery.order_id` (`order_id`,`order_version_id`),
              KEY `fk.order_delivery.shipping_method_id` (`shipping_method_id`),
              KEY `fk.order_delivery.shipping_order_address_id` (`shipping_order_address_id`,`shipping_order_address_version_id`),
              CONSTRAINT `fk.order_delivery.order_id` FOREIGN KEY (`order_id`, `order_version_id`) REFERENCES `order` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.order_delivery.shipping_method_id` FOREIGN KEY (`shipping_method_id`) REFERENCES `shipping_method` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `fk.order_delivery.shipping_order_address_id` FOREIGN KEY (`shipping_order_address_id`, `shipping_order_address_version_id`) REFERENCES `order_address` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.order_delivery.state_id` FOREIGN KEY (`state_id`) REFERENCES `state_machine_state` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `json.order_delivery.shipping_costs` CHECK (json_valid(`shipping_costs`)),
              CONSTRAINT `json.order_delivery.custom_fields` CHECK (json_valid(`custom_fields`)),
              CONSTRAINT `json.order_delivery.tracking_codes` CHECK (json_valid(`tracking_codes`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
