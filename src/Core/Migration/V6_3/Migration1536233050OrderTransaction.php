<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1536233050OrderTransaction extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536233050;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `order_transaction` (
              `id` binary(16) NOT NULL,
              `version_id` binary(16) NOT NULL,
              `order_id` binary(16) NOT NULL,
              `order_version_id` binary(16) NOT NULL,
              `state_id` binary(16) NOT NULL,
              `payment_method_id` binary(16) NOT NULL,
              `amount` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`amount`)),
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `validation_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`validation_data`)),
              PRIMARY KEY (`id`,`version_id`),
              KEY `idx.state_index` (`state_id`),
              KEY `fk.order_transaction.order_id` (`order_id`,`order_version_id`),
              KEY `fk.order_transaction.payment_method_id` (`payment_method_id`),
              KEY `idx.order_transaction_created_updated` (`created_at`,`updated_at`),
              CONSTRAINT `fk.order_transaction.order_id` FOREIGN KEY (`order_id`, `order_version_id`) REFERENCES `order` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.order_transaction.payment_method_id` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_method` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `fk.order_transaction.state_id` FOREIGN KEY (`state_id`) REFERENCES `state_machine_state` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `json.order_transaction.amount` CHECK (json_valid(`amount`)),
              CONSTRAINT `json.order_transaction.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
