<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_5;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('checkout')]
class Migration1674204177TaxProvider extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1674204177;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `tax_provider` (
              `id` binary(16) NOT NULL,
              `active` tinyint(1) unsigned NOT NULL DEFAULT 1,
              `priority` int(11) NOT NULL DEFAULT 1,
              `identifier` varchar(255) NOT NULL,
              `availability_rule_id` binary(16) DEFAULT NULL,
              `app_id` binary(16) DEFAULT NULL,
              `process_url` varchar(255) DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq.tax_provider.identifier` (`identifier`),
              KEY `fk.tax_provider.app_id` (`app_id`),
              KEY `availability_rule_id` (`availability_rule_id`,`active`),
              CONSTRAINT `fk.tax_provider.app_id` FOREIGN KEY (`app_id`) REFERENCES `app` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.tax_provider.availability_rule_id` FOREIGN KEY (`availability_rule_id`) REFERENCES `rule` (`id`) ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `tax_provider_translation` (
              `tax_provider_id` binary(16) NOT NULL,
              `language_id` binary(16) NOT NULL,
              `name` varchar(255) DEFAULT NULL,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`tax_provider_id`,`language_id`),
              KEY `fk.tax_provider_translation.language_id` (`language_id`),
              CONSTRAINT `fk.tax_provider_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.tax_provider_translation.tax_provider_id` FOREIGN KEY (`tax_provider_id`) REFERENCES `tax_provider` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.tax_provider_translation.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }
}
