<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1536233010OrderAddress extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536233010;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `order_address` (
              `id` binary(16) NOT NULL,
              `version_id` binary(16) NOT NULL,
              `country_id` binary(16) NOT NULL,
              `country_state_id` binary(16) DEFAULT NULL,
              `city_id` binary(16) DEFAULT NULL,
              `district_id` binary(16) DEFAULT NULL,
              `order_id` binary(16) NOT NULL,
              `order_version_id` binary(16) NOT NULL,
              `gender` tinyint(1) NOT NULL DEFAULT 0,
              `name` varchar(255) NOT NULL,
              `street` varchar(255) NOT NULL,
              `zipcode` varchar(50) DEFAULT NULL,
              `city` varchar(70) NOT NULL,
              `vat_id` varchar(50) DEFAULT NULL,
              `phone_number` varchar(40) DEFAULT NULL,
              `additional_address_line1` varchar(255) DEFAULT NULL,
              `additional_address_line2` varchar(255) DEFAULT NULL,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`,`version_id`),
              KEY `fk.order_address.country_id` (`country_id`),
              KEY `fk.order_address.country_state_id` (`country_state_id`),
              KEY `fk.order_address.order_id` (`order_id`,`order_version_id`),
              KEY `idx.order_address_created_updated` (`created_at`,`updated_at`),
              CONSTRAINT `fk.order_address.country_id` FOREIGN KEY (`country_id`) REFERENCES `country` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `fk.order_address.country_state_id` FOREIGN KEY (`country_state_id`) REFERENCES `country_state` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
              CONSTRAINT `fk.order_address.order_id` FOREIGN KEY (`order_id`, `order_version_id`) REFERENCES `order` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.order_address.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
