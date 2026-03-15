<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_8;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1773576046VirtualAsset extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1773576046;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `virtual_asset` (
              `id` binary(16) NOT NULL,
              `identifier` varchar(40) NOT NULL default \'customer\',
              `referenced_id` varchar(40) NOT NULL,
              `version` INT NOT NULL DEFAULT 1,
              `type` VARCHAR(32) NOT NULL DEFAULT \'points\',
              `balance` INT NOT NULL DEFAULT 0,
              `available_balance` INT NOT NULL DEFAULT 0,
              `frozen_balance` INT NOT NULL DEFAULT 0,
              `total_earned` INT NOT NULL DEFAULT 0,
              `total_spent` INT NOT NULL DEFAULT 0,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `active` tinyint(1) NOT NULL DEFAULT 0,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq.virtual_asset.identifier.referenced_id.type` (`identifier`, `referenced_id`, `type`),
              CONSTRAINT `json.virtual_asset.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `virtual_asset_transactions` (
              `id` binary(16) NOT NULL,
              `virtual_asset_id` BINARY(16) NOT NULL,
              `type` VARCHAR(32) NOT NULL,
              `direction` tinyint(1) NOT NULL DEFAULT 0,
              `amount` INT NOT NULL DEFAULT 0,
              `balance_before` INT NOT NULL DEFAULT 0,
              `balance_after` INT NOT NULL DEFAULT 0,
              `identifier` varchar(40)  NULL,
              `referenced_id` varchar(40) NULL,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              INDEX `idx_virtual_asset_transactions_identifier_referenced` (`identifier`, `referenced_id`),
              CONSTRAINT `fk.virtual_asset_transactions.virtual_asset_id` FOREIGN KEY (`virtual_asset_id`) REFERENCES `virtual_asset` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.virtual_asset_transactions.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }
}
