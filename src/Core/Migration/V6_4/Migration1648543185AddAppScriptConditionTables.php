<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_4;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;
use Shopwell\Core\Framework\Util\Database\TableHelper;

/**
 * @internal
 */
#[Package('framework')]
class Migration1648543185AddAppScriptConditionTables extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1648543185;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `app_script_condition` (
              `id` binary(16) NOT NULL,
              `app_id` binary(16) NOT NULL,
              `identifier` varchar(255) NOT NULL,
              `active` tinyint(1) NOT NULL DEFAULT 1,
              `group` varchar(255) DEFAULT NULL,
              `script` longtext DEFAULT NULL,
              `constraints` longblob DEFAULT NULL,
              `config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`config`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `fk.app_script_condition.app_id` (`app_id`),
              CONSTRAINT `fk.app_script_condition.app_id` FOREIGN KEY (`app_id`) REFERENCES `app` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `app_script_condition_translation` (
              `app_script_condition_id` binary(16) NOT NULL,
              `language_id` binary(16) NOT NULL,
              `name` varchar(255) DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`app_script_condition_id`,`language_id`),
              KEY `fk.app_script_condition_translation.app_script_condition_id` (`app_script_condition_id`),
              KEY `fk.app_script_condition_translation.language_id` (`language_id`),
              CONSTRAINT `fk.app_script_condition_translation.app_script_condition_id` FOREIGN KEY (`app_script_condition_id`) REFERENCES `app_script_condition` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.app_script_condition_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        if (!TableHelper::columnExists($connection, 'rule_condition', 'script_id')) {
            $connection->executeStatement('ALTER TABLE `rule_condition` ADD `script_id` BINARY(16) NULL AFTER rule_id');
            $connection->executeStatement('ALTER TABLE `rule_condition` ADD CONSTRAINT `fk.rule_condition.script_id` FOREIGN KEY (`script_id`)
              REFERENCES `app_script_condition` (`id`) ON DELETE SET NULL ON UPDATE CASCADE');
        }
    }
}
