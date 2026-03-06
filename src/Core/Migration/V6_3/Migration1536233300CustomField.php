<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1536233300CustomField extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536233300;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `custom_field` (
              `id` binary(16) NOT NULL,
              `name` varchar(255) NOT NULL,
              `type` varchar(255) NOT NULL,
              `config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`config`)),
              `active` tinyint(1) NOT NULL DEFAULT 1,
              `set_id` binary(16) DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `allow_customer_write` tinyint(4) NOT NULL DEFAULT 0,
              `allow_cart_expose` tinyint(1) NOT NULL DEFAULT 0,
              `store_api_aware` tinyint(1) NOT NULL DEFAULT 1,
              `include_in_search` tinyint(1) NOT NULL DEFAULT 0,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq.custom_field.name` (`name`),
              KEY `fk.custom_field.set_id` (`set_id`),
              CONSTRAINT `fk.custom_field.set_id` FOREIGN KEY (`set_id`) REFERENCES `custom_field_set` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.custom_field.config` CHECK (json_valid(`config`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
