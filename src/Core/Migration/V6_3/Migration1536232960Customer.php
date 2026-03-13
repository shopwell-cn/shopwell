<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1536232960Customer extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536232960;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<'SQL'
            CREATE TABLE `customer` (
              `id` binary(16) NOT NULL,
              `auto_increment` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `customer_group_id` binary(16) NOT NULL,
              `requested_customer_group_id` binary(16) DEFAULT NULL,
              `sales_channel_id` binary(16) NOT NULL,
              `language_id` binary(16) NOT NULL,
              `last_payment_method_id` binary(16) DEFAULT NULL,
              `default_billing_address_id` binary(16) NULL,
              `default_shipping_address_id` binary(16) NULL,
              `customer_number` varchar(255) NOT NULL,
              `gender` tinyint(1) NOT NULL DEFAULT 0,
              `nickname` varchar(255) NOT NULL,
              `company` varchar(255) DEFAULT NULL,
              `vat_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`vat_ids`)),
              `phone_number` varchar(255) NULL,
              `avatar_id` binary(16) DEFAULT NULL,
              `password` varchar(1024) DEFAULT NULL,
              `legacy_password` varchar(255) DEFAULT NULL,
              `legacy_encoder` varchar(255) DEFAULT NULL,
              `email` varchar(254) NOT NULL,
              `active` tinyint(1) NOT NULL DEFAULT 1,
              `double_opt_in_registration` tinyint(1) NOT NULL DEFAULT 0,
              `double_opt_in_email_sent_date` datetime(3) DEFAULT NULL,
              `double_opt_in_confirm_date` datetime(3) DEFAULT NULL,
              `hash` varchar(255) DEFAULT NULL,
              `guest` tinyint(1) NOT NULL DEFAULT 0,
              `first_login` datetime(3) DEFAULT NULL,
              `last_login` datetime(3) DEFAULT NULL,
              `birthday` date DEFAULT NULL,
              `last_order_date` datetime(3) DEFAULT NULL,
              `order_count` int(5) NOT NULL DEFAULT 0,
              `order_total_amount` double DEFAULT 0,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `affiliate_code` varchar(255) DEFAULT NULL,
              `campaign_code` varchar(255) DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `remote_address` varchar(255) DEFAULT NULL,
              `tag_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tag_ids`)),
              `bound_sales_channel_id` binary(16) DEFAULT NULL,
              `created_by_id` binary(16) DEFAULT NULL,
              `updated_by_id` binary(16) DEFAULT NULL,
              `account_type` varchar(255) NOT NULL DEFAULT 'private',
              `review_count` int(11) NOT NULL DEFAULT 0,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq.auto_increment` (`auto_increment`),
              UNIQUE KEY `hash` (`hash`),
              KEY `idx.firstlogin` (`first_login`),
              KEY `idx.lastlogin` (`last_login`),
              KEY `idx.customer.default_billing_address_id` (`default_billing_address_id`),
              KEY `idx.customer.default_shipping_address_id` (`default_shipping_address_id`),
              KEY `fk.customer.customer_group_id` (`customer_group_id`),
              KEY `fk.customer.last_payment_method_id` (`last_payment_method_id`),
              KEY `fk.customer.sales_channel_id` (`sales_channel_id`),
              KEY `fk.customer.requested_customer_group_id` (`requested_customer_group_id`),
              KEY `fk.customer.bound_sales_channel_id` (`bound_sales_channel_id`),
              KEY `idx.email` (`email`),
              KEY `fk.customer.created_by_id` (`created_by_id`),
              KEY `fk.customer.updated_by_id` (`updated_by_id`),
              CONSTRAINT `fk.customer.avatar_id` FOREIGN KEY (`avatar_id`) REFERENCES `media` (`id`) ON DELETE SET NULL,
              CONSTRAINT `fk.customer.bound_sales_channel_id` FOREIGN KEY (`bound_sales_channel_id`) REFERENCES `sales_channel` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `fk.customer.created_by_id` FOREIGN KEY (`created_by_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
              CONSTRAINT `fk.customer.customer_group_id` FOREIGN KEY (`customer_group_id`) REFERENCES `customer_group` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `fk.customer.last_payment_method_id` FOREIGN KEY (`last_payment_method_id`) REFERENCES `payment_method` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `fk.customer.sales_channel_id` FOREIGN KEY (`sales_channel_id`) REFERENCES `sales_channel` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `fk.customer.updated_by_id` FOREIGN KEY (`updated_by_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
              CONSTRAINT `json.customer.custom_fields` CHECK (json_valid(`custom_fields`)),
              CONSTRAINT `json.customer.tag_ids` CHECK (json_valid(`tag_ids`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($sql);

        $connection->executeStatement('
            CREATE TABLE `customer_address` (
              `id` binary(16) NOT NULL,
              `customer_id` binary(16) NOT NULL,
              `country_id` binary(16) NOT NULL,
              `country_state_id` binary(16) DEFAULT NULL,
              `city_id` binary(16) DEFAULT NULL,
              `district_id` binary(16) DEFAULT NULL,
              `company` varchar(255) DEFAULT NULL,
              `department` varchar(255) DEFAULT NULL,
              `gender` varchar(100) DEFAULT NULL,
              `name` varchar(255) NOT NULL,
              `street` varchar(255) NOT NULL,
              `zipcode` varchar(50) DEFAULT NULL,
              `city` varchar(70) NOT NULL,
              `phone_number` varchar(40) DEFAULT NULL,
              `additional_address_line1` varchar(255) DEFAULT NULL,
              `additional_address_line2` varchar(255) DEFAULT NULL,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `fk.customer_address.country_id` (`country_id`),
              KEY `fk.customer_address.country_state_id` (`country_state_id`),
              KEY `fk.customer_address.customer_id` (`customer_id`),
              CONSTRAINT `fk.customer_address.country_id` FOREIGN KEY (`country_id`) REFERENCES `country` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `fk.customer_address.country_state_id` FOREIGN KEY (`country_state_id`) REFERENCES `country_state` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
              CONSTRAINT `fk.customer_address.customer_id` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.customer_address.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
