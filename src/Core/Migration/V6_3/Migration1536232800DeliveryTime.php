<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1536232800DeliveryTime extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536232800;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `delivery_time` (
              `id` binary(16) NOT NULL,
              `min` int(3) NOT NULL,
              `max` int(3) NOT NULL,
              `unit` varchar(255) NOT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `delivery_time_translation` (
              `delivery_time_id` binary(16) NOT NULL,
              `language_id` binary(16) NOT NULL,
              `name` varchar(255) DEFAULT NULL,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`delivery_time_id`,`language_id`),
              KEY `fk.delivery_time_translation.language_id` (`language_id`),
              CONSTRAINT `fk.delivery_time_translation.delivery_time_id` FOREIGN KEY (`delivery_time_id`) REFERENCES `delivery_time` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.delivery_time_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // nth
    }
}
