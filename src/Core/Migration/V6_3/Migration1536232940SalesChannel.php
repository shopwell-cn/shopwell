<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1536232940SalesChannel extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536232940;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<'SQL'
            CREATE TABLE `sales_channel` (
              `id` binary(16) NOT NULL,
              `type_id` binary(16) NOT NULL,
              `short_name` varchar(45) DEFAULT NULL,
              `configuration` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`configuration`)),
              `access_key` varchar(255) NOT NULL,
              `language_id` binary(16) NOT NULL,
              `currency_id` binary(16) NOT NULL,
              `payment_method_id` binary(16) NOT NULL,
              `shipping_method_id` binary(16) NOT NULL,
              `country_id` binary(16) NOT NULL,
              `navigation_category_id` binary(16) NOT NULL,
              `navigation_category_version_id` binary(16) NOT NULL,
              `navigation_category_depth` int(11) NOT NULL DEFAULT 2,
              `hreflang_active` tinyint(1) unsigned DEFAULT 0,
              `footer_category_id` binary(16) DEFAULT NULL,
              `footer_category_version_id` binary(16) DEFAULT NULL,
              `service_category_id` binary(16) DEFAULT NULL,
              `service_category_version_id` binary(16) DEFAULT NULL,
              `active` tinyint(1) NOT NULL DEFAULT 1,
              `maintenance` tinyint(1) NOT NULL DEFAULT 0,
              `maintenance_ip_whitelist` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`maintenance_ip_whitelist`)),
              `customer_group_id` binary(16) NOT NULL,
              `mail_header_footer_id` binary(16) DEFAULT NULL,
              `payment_method_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payment_method_ids`)),
              `tax_calculation_type` varchar(50) NOT NULL DEFAULT 'horizontal',
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `measurement_units` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`measurement_units`)),
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq.access_key` (`access_key`),
              KEY `fk.sales_channel.country_id` (`country_id`),
              KEY `fk.sales_channel.currency_id` (`currency_id`),
              KEY `fk.sales_channel.language_id` (`language_id`),
              KEY `fk.sales_channel.payment_method_id` (`payment_method_id`),
              KEY `fk.sales_channel.shipping_method_id` (`shipping_method_id`),
              KEY `fk.sales_channel.type_id` (`type_id`),
              KEY `fk.sales_channel.navigation_category_id` (`navigation_category_id`,`navigation_category_version_id`),
              KEY `fk.sales_channel.footer_category_id` (`footer_category_id`,`footer_category_version_id`),
              KEY `fk.sales_channel.service_category_id` (`service_category_id`,`service_category_version_id`),
              KEY `fk.sales_channel.customer_group_id` (`customer_group_id`),
              KEY `fk.sales_channel.header_footer_id` (`mail_header_footer_id`),
              CONSTRAINT `fk.sales_channel.country_id` FOREIGN KEY (`country_id`) REFERENCES `country` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `fk.sales_channel.currency_id` FOREIGN KEY (`currency_id`) REFERENCES `currency` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `fk.sales_channel.customer_group_id` FOREIGN KEY (`customer_group_id`) REFERENCES `customer_group` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `fk.sales_channel.footer_category_id` FOREIGN KEY (`footer_category_id`, `footer_category_version_id`) REFERENCES `category` (`id`, `version_id`) ON UPDATE CASCADE,
              CONSTRAINT `fk.sales_channel.header_footer_id` FOREIGN KEY (`mail_header_footer_id`) REFERENCES `mail_header_footer` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `fk.sales_channel.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `fk.sales_channel.navigation_category_id` FOREIGN KEY (`navigation_category_id`, `navigation_category_version_id`) REFERENCES `category` (`id`, `version_id`) ON UPDATE CASCADE,
              CONSTRAINT `fk.sales_channel.payment_method_id` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_method` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `fk.sales_channel.service_category_id` FOREIGN KEY (`service_category_id`, `service_category_version_id`) REFERENCES `category` (`id`, `version_id`) ON UPDATE CASCADE,
              CONSTRAINT `fk.sales_channel.shipping_method_id` FOREIGN KEY (`shipping_method_id`) REFERENCES `shipping_method` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `fk.sales_channel.type_id` FOREIGN KEY (`type_id`) REFERENCES `sales_channel_type` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `json.sales_channel.configuration` CHECK (json_valid(`configuration`)),
              CONSTRAINT `json.sales_channel.payment_method_ids` CHECK (json_valid(`payment_method_ids`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($sql);

        $connection->executeStatement('
            CREATE TABLE `sales_channel_translation` (
              `sales_channel_id` binary(16) NOT NULL,
              `language_id` binary(16) NOT NULL,
              `name` varchar(255) DEFAULT NULL,
              `home_keywords` varchar(255) DEFAULT NULL,
              `home_meta_description` varchar(255) DEFAULT NULL,
              `home_meta_title` varchar(255) DEFAULT NULL,
              `home_name` varchar(255) DEFAULT NULL,
              `home_enabled` tinyint(4) NOT NULL DEFAULT 1,
              `home_slot_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`home_slot_config`)),
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`sales_channel_id`,`language_id`),
              KEY `fk.sales_channel_translation.language_id` (`language_id`),
              CONSTRAINT `fk.sales_channel_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.sales_channel_translation.sales_channel_id` FOREIGN KEY (`sales_channel_id`) REFERENCES `sales_channel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.sales_channel_translation.custom_fields` CHECK (json_valid(`custom_fields`)),
              CONSTRAINT `json.sales_channel_translation.home_slot_config` CHECK (json_valid(`home_slot_config`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `sales_channel_language` (
              `sales_channel_id` BINARY(16) NOT NULL,
              `language_id` BINARY(16) NOT NULL,
              PRIMARY KEY (`sales_channel_id`, `language_id`),
              CONSTRAINT `fk.sales_channel_language.sales_channel_id` FOREIGN KEY (`sales_channel_id`)
                REFERENCES `sales_channel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.sales_channel_language.language_id` FOREIGN KEY (`language_id`)
                REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `sales_channel_currency` (
              `sales_channel_id` BINARY(16) NOT NULL,
              `currency_id` BINARY(16) NOT NULL,
              PRIMARY KEY (`sales_channel_id`, `currency_id`),
              CONSTRAINT `fk.sales_channel_currency.sales_channel_id` FOREIGN KEY (`sales_channel_id`)
                REFERENCES `sales_channel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.sales_channel_currency.currency_id` FOREIGN KEY (`currency_id`)
                REFERENCES `currency` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `sales_channel_country` (
              `sales_channel_id` BINARY(16) NOT NULL,
              `country_id` BINARY(16) NOT NULL,
              PRIMARY KEY (`sales_channel_id`, `country_id`),
              CONSTRAINT `fk.sales_channel_country.sales_channel_id` FOREIGN KEY (`sales_channel_id`)
                REFERENCES `sales_channel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.sales_channel_country.country_id` FOREIGN KEY (`country_id`)
                REFERENCES `country` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `sales_channel_shipping_method` (
              `sales_channel_id` BINARY(16) NOT NULL,
              `shipping_method_id` BINARY(16) NOT NULL,
              PRIMARY KEY (`sales_channel_id`, `shipping_method_id`),
              CONSTRAINT `fk.sales_channel_shipping_method.sales_channel_id` FOREIGN KEY (`sales_channel_id`)
                REFERENCES `sales_channel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.sales_channel_shipping_method.shipping_method_id` FOREIGN KEY (`shipping_method_id`)
                REFERENCES `shipping_method` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `sales_channel_payment_method` (
              `sales_channel_id` BINARY(16) NOT NULL,
              `payment_method_id` BINARY(16) NOT NULL,
              PRIMARY KEY (`sales_channel_id`, `payment_method_id`),
              CONSTRAINT `fk.sales_channel_payment_method.sales_channel_id` FOREIGN KEY (`sales_channel_id`)
                REFERENCES `sales_channel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.sales_channel_payment_method.payment_method_id` FOREIGN KEY (`payment_method_id`)
                REFERENCES `payment_method` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
