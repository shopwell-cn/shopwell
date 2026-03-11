<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_8;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;
use Shopwell\Core\Framework\Util\Database\TableHelper;

/**
 * @internal
 */
#[Package('framework')]
class Migration1773244554PaymentOrder extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1773244554;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<'SQL'
        CREATE TABLE IF NOT EXISTS `payment_order` (
              `id` BINARY(16) NOT NULL,
              `version_id` binary(16) NOT NULL,
              `out_order_no` varchar(100) NOT NULL,
              `payment_order_number` varchar(100) NOT NULL,
              `state_id` binary(16) NOT NULL,
              `amount` DECIMAL(10,2) NOT NULL,
              `subject` varchar(100) NOT NULL,
              `currency` varchar(100) NOT NULL DEFAULT 'CNY',
              `body` mediumtext DEFAULT NULL,
              `return_url` varchar(200) NULL,
              `notify_url` varchar(200) NULL,
              `extra_param` JSON NULL,
              `attach` JSON NULL,
              `time_expire` DATETIME(3) NULL,
              `custom_fields` JSON NULL,
              `created_at` DATETIME(3) NOT NULL,
              `updated_at` DATETIME(3) NULL,
              PRIMARY KEY (`id`,`version_id`),
              UNIQUE INDEX `unique.payment_order.out_order_no` (`out_order_no`),
              UNIQUE INDEX `unique.payment_order.payment_order_number` (`payment_order_number`),
              CONSTRAINT `fk.payment_order.state_id` FOREIGN KEY (`state_id`) REFERENCES `state_machine_state` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `json.payment_order.custom_fields` CHECK (JSON_VALID(`custom_fields`)),
              CONSTRAINT `json.payment_order.extra_param` CHECK (JSON_VALID(`extra_param`)),
              CONSTRAINT `json.payment_order.attach` CHECK (JSON_VALID(`attach`))
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<'SQL'
        CREATE TABLE IF NOT EXISTS `payment_order_transaction` (
              `id` BINARY(16) NOT NULL,
              `version_id` binary(16) NOT NULL,
              `payment_order_id` binary(16) NOT NULL,
              `payment_order_version_id` binary(16) NOT NULL,
              `state_id` binary(16) NOT NULL,
              `amount` DECIMAL(10,2) NOT NULL,
              `channel` varchar(64) NOT NULL,
              `payment_type` varchar(64) NOT NULL,
              `channel_trade_no` varchar(64) NULL,
              `custom_fields` JSON NULL,
              `created_at` DATETIME(3) NOT NULL,
              `updated_at` DATETIME(3) NULL,
              PRIMARY KEY (`id`,`version_id`),
              UNIQUE INDEX `unique.channel` (`channel`,`channel_trade_no`),
              CONSTRAINT `fk.payment_order_transaction.payment_order_version_id` FOREIGN KEY (`payment_order_id`, `payment_order_version_id`) REFERENCES `payment_order` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.payment_order_transaction.state_id` FOREIGN KEY (`state_id`) REFERENCES `state_machine_state` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `json.payment_order_transaction.custom_fields` CHECK (JSON_VALID(`custom_fields`))
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        if (!TableHelper::columnExists($connection, 'payment_order', 'primary_order_transaction_id')) {
            $connection->executeStatement(
                'ALTER TABLE `payment_order`
                ADD COLUMN `primary_order_transaction_id` BINARY(16) NULL DEFAULT NULL,
                ADD COLUMN `primary_order_transaction_version_id` BINARY(16) NULL DEFAULT NULL,
                ADD UNIQUE INDEX `uidx.payment_order.primary_order_transaction` (`id`, `version_id`, `primary_order_transaction_id`)'
            );
        }
    }
}
