<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1536232920PaymentMethod extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536232920;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `payment_method` (
              `id` binary(16) NOT NULL,
              `handler_identifier` varchar(255) NOT NULL DEFAULT \'Shopwell\\Core\\Checkout\\Payment\\Cart\\PaymentHandler\\DefaultPayment\',
              `position` int(11) NOT NULL DEFAULT 1,
              `active` tinyint(1) NOT NULL DEFAULT 0,
              `after_order_enabled` tinyint(1) NOT NULL DEFAULT 0,
              `availability_rule_id` binary(16) DEFAULT NULL,
              `plugin_id` binary(16) DEFAULT NULL,
              `media_id` binary(16) DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `technical_name` varchar(255) NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq.technical_name` (`technical_name`),
              KEY `fk.payment_method.plugin_id` (`plugin_id`),
              KEY `fk.payment_method.media_id` (`media_id`),
              KEY `fk.payment_method.availability_rule_id` (`availability_rule_id`),
              CONSTRAINT `fk.payment_method.availability_rule_id` FOREIGN KEY (`availability_rule_id`) REFERENCES `rule` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `fk.payment_method.media_id` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
              CONSTRAINT `fk.payment_method.plugin_id` FOREIGN KEY (`plugin_id`) REFERENCES `plugin` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `payment_method_translation` (
              `payment_method_id` binary(16) NOT NULL,
              `language_id` binary(16) NOT NULL,
              `name` varchar(255) DEFAULT NULL,
              `distinguishable_name` varchar(255) DEFAULT NULL,
              `description` mediumtext DEFAULT NULL,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`payment_method_id`,`language_id`),
              KEY `fk.payment_method_translation.language_id` (`language_id`),
              CONSTRAINT `fk.payment_method_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.payment_method_translation.payment_method_id` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_method` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.payment_method_translation.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
