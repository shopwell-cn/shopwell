<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_8;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1773153289Wallet extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1773153289;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<'SQL'
        CREATE TABLE IF NOT EXISTS `wallet` (
              `id` BINARY(16) NOT NULL,
              `version` INT NOT NULL DEFAULT 1,
              `active` tinyint(1) NOT NULL DEFAULT 1,
              `identifier` varchar(40) NOT NULL default 'customer',
              `referenced_id` varchar(40) NOT NULL,
              `currency_id` binary(16) NOT NULL,
              `balance` DECIMAL(20,8) NOT NULL DEFAULT 0,
              `available_balance` DECIMAL(20,8) NOT NULL DEFAULT 0,
              `frozen_balance` DECIMAL(20,8) NOT NULL DEFAULT 0,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` DATETIME(3) NOT NULL,
              `updated_at` DATETIME(3) NULL,
              PRIMARY KEY (`id`),
              INDEX `idx_wallet_identifier_referenced` (`identifier`, `referenced_id`),
              CONSTRAINT `json.wallet.custom_fields` CHECK (json_valid(`custom_fields`)),
              CONSTRAINT `fk.wallet.currency_id` FOREIGN KEY (`currency_id`) REFERENCES `currency` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL;
        $connection->executeStatement($sql);

        $sql = <<<'SQL'
        CREATE TABLE IF NOT EXISTS `wallet_transaction` (
              `id` BINARY(16) NOT NULL,
              `wallet_id` binary(16) NOT NULL,
              `type` VARCHAR(32) NOT NULL,
              `amount` DECIMAL(20,8) NOT NULL DEFAULT 0,
              `balance_before` DECIMAL(20,8) NOT NULL DEFAULT 0,
              `balance_after` DECIMAL(20,8) NOT NULL DEFAULT 0,
              `identifier` varchar(40) NOT NULL,
              `referenced_id` varchar(40) NOT NULL,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` DATETIME(3) NOT NULL,
              `updated_at` DATETIME(3) NULL,
              PRIMARY KEY (`id`),
              CONSTRAINT `json.wallet_transaction.custom_fields` CHECK (json_valid(`custom_fields`)),
              CONSTRAINT `fk.wallet_transaction.wallet_id` FOREIGN KEY (`wallet_id`) REFERENCES `wallet` (`id`) ON UPDATE CASCADE
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL;
        $connection->executeStatement($sql);

        $sql = <<<'SQL'
        CREATE TABLE IF NOT EXISTS `wallet_freeze` (
              `id` BINARY(16) NOT NULL,
              `wallet_id` binary(16) NOT NULL,
              `amount` DECIMAL(20,8) NOT NULL DEFAULT 0,
              `identifier` varchar(40) NOT NULL,
              `referenced_id` varchar(40) NOT NULL,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` DATETIME(3) NOT NULL,
              `updated_at` DATETIME(3) NULL,
              PRIMARY KEY (`id`),
              CONSTRAINT `json.wallet_freeze.custom_fields` CHECK (json_valid(`custom_fields`)),
              CONSTRAINT `fk.wallet_freeze.wallet_id` FOREIGN KEY (`wallet_id`) REFERENCES `wallet` (`id`) ON UPDATE CASCADE
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL;
        $connection->executeStatement($sql);

        $sql = <<<'SQL'
        CREATE TABLE IF NOT EXISTS `wallet_withdraw` (
              `id` BINARY(16) NOT NULL,
              `wallet_id` binary(16) NOT NULL,
              `amount` DECIMAL(20,8) NOT NULL DEFAULT 0,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` DATETIME(3) NOT NULL,
              `updated_at` DATETIME(3) NULL,
              PRIMARY KEY (`id`),
              CONSTRAINT `json.wallet_withdraw.custom_fields` CHECK (json_valid(`custom_fields`)),
              CONSTRAINT `fk.wallet_withdraw.wallet_id` FOREIGN KEY (`wallet_id`) REFERENCES `wallet` (`id`) ON UPDATE CASCADE
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL;
        $connection->executeStatement($sql);

        $sql = <<<'SQL'
            CREATE TABLE `wallet_withdraw_method` (
              `id` binary(16) NOT NULL,
              `handler_identifier` varchar(255) NOT NULL DEFAULT 'ShopwellCoreCheckoutPaymentCartPaymentHandlerDefaultPayment',
              `position` int(11) NOT NULL DEFAULT 1,
              `active` tinyint(1) NOT NULL DEFAULT 0,
              `availability_rule_id` binary(16) DEFAULT NULL,
              `plugin_id` binary(16) DEFAULT NULL,
              `media_id` binary(16) DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `technical_name` varchar(255) NOT NULL,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq.technical_name` (`technical_name`),
              KEY `fk.wallet_withdraw_method.plugin_id` (`plugin_id`),
              KEY `fk.wallet_withdraw_method.media_id` (`media_id`),
              KEY `fk.wallet_withdraw_method.availability_rule_id` (`availability_rule_id`),
              CONSTRAINT `json.wallet_withdraw_method.custom_fields` CHECK (json_valid(`custom_fields`)),
              CONSTRAINT `fk.wallet_withdraw_method.availability_rule_id` FOREIGN KEY (`availability_rule_id`) REFERENCES `rule` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `fk.wallet_withdraw_method.media_id` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
              CONSTRAINT `fk.wallet_withdraw_method.plugin_id` FOREIGN KEY (`plugin_id`) REFERENCES `plugin` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL;
        $connection->executeStatement($sql);

        $sql = <<<'SQL'
        CREATE TABLE IF NOT EXISTS `wallet_customer_withdraw_method` (
              `id` BINARY(16) NOT NULL,
              `customer_id` binary(16) NOT NULL,
              `withdraw_method_id` binary(16) NOT NULL,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` DATETIME(3) NOT NULL,
              `updated_at` DATETIME(3) NULL,
              PRIMARY KEY (`id`),
              CONSTRAINT `json.wallet_customer_withdraw_method.custom_fields` CHECK (json_valid(`custom_fields`)),
              CONSTRAINT `fk.wallet_customer_withdraw_method.customer_id` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `fk.wallet_customer_withdraw_method.withdraw_method_id` FOREIGN KEY (`withdraw_method_id`) REFERENCES `wallet_withdraw_method` (`id`) ON UPDATE CASCADE
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL;
        $connection->executeStatement($sql);
    }
}
