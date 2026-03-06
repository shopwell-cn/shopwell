<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1597391970App extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1597391970;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `app` (
              `id` binary(16) NOT NULL,
              `name` varchar(255) NOT NULL,
              `path` varchar(4096) DEFAULT NULL,
              `author` varchar(255) DEFAULT NULL,
              `copyright` varchar(255) DEFAULT NULL,
              `license` varchar(255) DEFAULT NULL,
              `privacy` varchar(255) DEFAULT NULL,
              `version` varchar(255) NOT NULL,
              `base_app_url` varchar(1024) DEFAULT NULL,
              `active` tinyint(1) NOT NULL DEFAULT 0,
              `allow_disable` tinyint(1) NOT NULL DEFAULT 1,
              `configurable` tinyint(1) NOT NULL DEFAULT 0,
              `icon` mediumblob DEFAULT NULL,
              `app_secret` varchar(255) DEFAULT NULL,
              `modules` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`modules`)),
              `main_module` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`main_module`)),
              `cookies` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`cookies`)),
              `allowed_hosts` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`allowed_hosts`)),
              `integration_id` binary(16) NOT NULL,
              `acl_role_id` binary(16) NOT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `template_load_priority` int(11) DEFAULT 0,
              `checkout_gateway_url` varchar(255) DEFAULT NULL,
              `self_managed` tinyint(1) NOT NULL DEFAULT 0,
              `source_type` varchar(20) NOT NULL DEFAULT \'local\',
              `source_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT json_object() CHECK (json_valid(`source_config`)),
              `in_app_purchases_gateway_url` varchar(255) DEFAULT NULL,
              `requested_privileges` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT json_array() CHECK (json_valid(`requested_privileges`)),
              `context_gateway_url` varchar(255) DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq.name` (`name`),
              KEY `fk.app.integration_id` (`integration_id`),
              KEY `fk.app.acl_role_id` (`acl_role_id`),
              CONSTRAINT `fk.app.acl_role_id` FOREIGN KEY (`acl_role_id`) REFERENCES `acl_role` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `fk.app.integration_id` FOREIGN KEY (`integration_id`) REFERENCES `integration` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `json.app.modules` CHECK (json_valid(`modules`)),
              CONSTRAINT `json.app.cookies` CHECK (json_valid(`cookies`)),
              CONSTRAINT `json.app.main_module` CHECK (json_valid(`main_module`)),
              CONSTRAINT `json.app.allowed_hosts` CHECK (json_valid(`allowed_hosts`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `app_translation` (
              `app_id` binary(16) NOT NULL,
              `language_id` binary(16) NOT NULL,
              `label` varchar(255) DEFAULT NULL,
              `description` longtext DEFAULT NULL,
              `privacy_policy_extensions` mediumtext DEFAULT NULL,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`app_id`,`language_id`),
              KEY `fk.app_translation.app_id` (`app_id`),
              KEY `fk.app_translation.language_id` (`language_id`),
              CONSTRAINT `fk.app_translation.app_id` FOREIGN KEY (`app_id`) REFERENCES `app` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.app_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
