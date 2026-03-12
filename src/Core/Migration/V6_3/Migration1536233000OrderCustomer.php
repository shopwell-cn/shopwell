<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1536233000OrderCustomer extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536233000;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `order_customer` (
              `id` binary(16) NOT NULL,
              `version_id` binary(16) NOT NULL,
              `customer_id` binary(16) DEFAULT NULL,
              `order_id` binary(16) NOT NULL,
              `order_version_id` binary(16) NOT NULL,
              `email` varchar(254) NOT NULL,
              `gender` tinyint(1) NOT NULL DEFAULT 0,
              `nickname` varchar(255) NOT NULL,
              `name` varchar(255) NULL,
              `phone_number` varchar(100) DEFAULT NULL,
              `customer_number` varchar(255) DEFAULT NULL,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `remote_address` varchar(255) DEFAULT NULL,
              PRIMARY KEY (`id`,`version_id`),
              KEY `fk.order_customer.customer_id` (`customer_id`),
              KEY `fk.order_customer.order_id` (`order_id`,`order_version_id`),
              CONSTRAINT `fk.order_customer.customer_id` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
              CONSTRAINT `fk.order_customer.order_id` FOREIGN KEY (`order_id`, `order_version_id`) REFERENCES `order` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.order_customer.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
