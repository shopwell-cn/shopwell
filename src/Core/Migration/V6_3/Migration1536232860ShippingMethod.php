<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1536232860ShippingMethod extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536232860;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `shipping_method` (
              `id` binary(16) NOT NULL,
              `active` tinyint(1) unsigned NOT NULL DEFAULT 0,
              `position` int(11) NOT NULL DEFAULT 1,
              `availability_rule_id` binary(16) DEFAULT NULL,
              `media_id` binary(16) DEFAULT NULL,
              `delivery_time_id` binary(16) NOT NULL,
              `tax_type` varchar(50) DEFAULT \'auto\',
              `tax_id` binary(16) DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `technical_name` varchar(255) NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq.technical_name` (`technical_name`),
              KEY `fk.shipping_method.media_id` (`media_id`),
              KEY `fk.shipping_method.availability_rule_id` (`availability_rule_id`),
              KEY `fk.shipping_method.delivery_time_id` (`delivery_time_id`),
              KEY `fk.shipping_method.tax_id` (`tax_id`),
              CONSTRAINT `fk.shipping_method.availability_rule_id` FOREIGN KEY (`availability_rule_id`) REFERENCES `rule` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `fk.shipping_method.delivery_time_id` FOREIGN KEY (`delivery_time_id`) REFERENCES `delivery_time` (`id`),
              CONSTRAINT `fk.shipping_method.media_id` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
              CONSTRAINT `fk.shipping_method.tax_id` FOREIGN KEY (`tax_id`) REFERENCES `tax` (`id`) ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `shipping_method_translation` (
              `shipping_method_id` binary(16) NOT NULL,
              `language_id` binary(16) NOT NULL,
              `name` varchar(255) DEFAULT NULL,
              `description` mediumtext DEFAULT NULL,
              `tracking_url` mediumtext DEFAULT NULL,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`shipping_method_id`,`language_id`),
              KEY `fk.shipping_method_translation.language_id` (`language_id`),
              CONSTRAINT `fk.shipping_method_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.shipping_method_translation.shipping_method_id` FOREIGN KEY (`shipping_method_id`) REFERENCES `shipping_method` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.shipping_method_translation.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `shipping_method_price` (
              `id` binary(16) NOT NULL,
              `shipping_method_id` binary(16) NOT NULL,
              `calculation` int(1) unsigned DEFAULT NULL,
              `rule_id` binary(16) DEFAULT NULL,
              `calculation_rule_id` binary(16) DEFAULT NULL,
              `currency_price` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`currency_price`)),
              `quantity_start` double DEFAULT NULL,
              `quantity_end` double DEFAULT NULL,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq.shipping_method_quantity_start` (`shipping_method_id`,`rule_id`,`quantity_start`),
              KEY `fk.shipping_method_price.rule_id` (`rule_id`),
              KEY `fk.shipping_method_price.calculation_rule_id` (`calculation_rule_id`),
              CONSTRAINT `fk.shipping_method_price.calculation_rule_id` FOREIGN KEY (`calculation_rule_id`) REFERENCES `rule` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `fk.shipping_method_price.rule_id` FOREIGN KEY (`rule_id`) REFERENCES `rule` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `fk.shipping_method_price.shipping_method_id` FOREIGN KEY (`shipping_method_id`) REFERENCES `shipping_method` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.shipping_method_price.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
