<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1536232990Order extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536232990;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `order` (
              `id` binary(16) NOT NULL,
              `version_id` binary(16) NOT NULL,
              `state_id` binary(16) NOT NULL,
              `auto_increment` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `order_number` varchar(64) DEFAULT NULL,
              `currency_id` binary(16) NOT NULL,
              `language_id` binary(16) NOT NULL,
              `currency_factor` double DEFAULT NULL,
              `sales_channel_id` binary(16) NOT NULL,
              `billing_address_id` binary(16) NOT NULL,
              `billing_address_version_id` binary(16) NOT NULL,
              `price` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`price`)),
              `order_date_time` datetime(3) NOT NULL,
              `order_date` date GENERATED ALWAYS AS (cast(`order_date_time` as date)) STORED,
              `amount_total` double GENERATED ALWAYS AS (json_unquote(json_extract(`price`,\'$.totalPrice\'))) VIRTUAL,
              `amount_net` double GENERATED ALWAYS AS (json_unquote(json_extract(`price`,\'$.netPrice\'))) VIRTUAL,
              `position_price` double GENERATED ALWAYS AS (json_unquote(json_extract(`price`,\'$.positionPrice\'))) VIRTUAL,
              `tax_status` varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`price`,\'$.taxStatus\'))) VIRTUAL,
              `shipping_costs` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`shipping_costs`)),
              `shipping_total` double GENERATED ALWAYS AS (json_unquote(json_extract(`shipping_costs`,\'$.totalPrice\'))) VIRTUAL,
              `deep_link_code` varchar(32) DEFAULT NULL,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `affiliate_code` varchar(255) DEFAULT NULL,
              `campaign_code` varchar(255) DEFAULT NULL,
              `customer_comment` longtext DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `item_rounding` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`item_rounding`)),
              `total_rounding` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`total_rounding`)),
              `rule_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`rule_ids`)),
              `created_by_id` binary(16) DEFAULT NULL,
              `updated_by_id` binary(16) DEFAULT NULL,
              `source` varchar(255) DEFAULT NULL,
              `primary_order_delivery_id` binary(16) DEFAULT NULL,
              `primary_order_delivery_version_id` binary(16) DEFAULT NULL,
              `primary_order_transaction_id` binary(16) DEFAULT NULL,
              `primary_order_transaction_version_id` binary(16) DEFAULT NULL,
              `internal_comment` longtext DEFAULT NULL,
              `tax_calculation_type` varchar(50) DEFAULT NULL,
              PRIMARY KEY (`id`,`version_id`),
              UNIQUE KEY `uniq.auto_increment` (`auto_increment`),
              UNIQUE KEY `uniq.deep_link_code` (`deep_link_code`,`version_id`),
              UNIQUE KEY `uidx.order.primary_order_delivery` (`id`,`version_id`,`primary_order_delivery_id`),
              UNIQUE KEY `uidx.order.primary_order_transaction` (`id`,`version_id`,`primary_order_transaction_id`),
              KEY `idx.state_index` (`state_id`),
              KEY `fk.order.currency_id` (`currency_id`),
              KEY `fk.order.sales_channel_id` (`sales_channel_id`),
              KEY `fk.language_id` (`language_id`),
              KEY `idx.order_date_currency_id` (`order_date`,`currency_id`),
              KEY `idx.order_number` (`order_number`),
              KEY `fk.order.created_by_id` (`created_by_id`),
              KEY `fk.order.updated_by_id` (`updated_by_id`),
              CONSTRAINT `fk.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `fk.order.created_by_id` FOREIGN KEY (`created_by_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
              CONSTRAINT `fk.order.currency_id` FOREIGN KEY (`currency_id`) REFERENCES `currency` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `fk.order.sales_channel_id` FOREIGN KEY (`sales_channel_id`) REFERENCES `sales_channel` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `fk.order.state_id` FOREIGN KEY (`state_id`) REFERENCES `state_machine_state` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `fk.order.updated_by_id` FOREIGN KEY (`updated_by_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
              CONSTRAINT `char_length.order.deep_link_code` CHECK (char_length(`deep_link_code`) = 32),
              CONSTRAINT `json.order.price` CHECK (json_valid(`price`)),
              CONSTRAINT `json.order.shipping_costs` CHECK (json_valid(`shipping_costs`)),
              CONSTRAINT `json.order.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
