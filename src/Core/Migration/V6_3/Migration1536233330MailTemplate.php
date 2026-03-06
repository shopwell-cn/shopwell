<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1536233330MailTemplate extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536233330;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `mail_template_type` (
              `id` binary(16) NOT NULL,
              `technical_name` varchar(255) NOT NULL,
              `available_entities` longtext DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `template_data` longtext DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq.mail_template_type.technical_name` (`technical_name`),
              CONSTRAINT `json.mail_template_type.available_entities` CHECK (json_valid(`available_entities`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `mail_template_type_translation` (
              `mail_template_type_id` binary(16) NOT NULL,
              `language_id` binary(16) NOT NULL,
              `name` varchar(255) DEFAULT NULL,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`mail_template_type_id`,`language_id`),
              KEY `fk.mail_template_type_translation.language_id` (`language_id`),
              CONSTRAINT `fk.mail_template_type_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.mail_template_type_translation.mail_template_type_id` FOREIGN KEY (`mail_template_type_id`) REFERENCES `mail_template_type` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.mail_template_type_translation.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `mail_template` (
              `id` binary(16) NOT NULL,
              `mail_template_type_id` binary(16) DEFAULT NULL,
              `system_default` tinyint(1) unsigned NOT NULL DEFAULT 0,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `fk.mail_template.mail_template_type_id` (`mail_template_type_id`),
              CONSTRAINT `fk.mail_template.mail_template_type_id` FOREIGN KEY (`mail_template_type_id`) REFERENCES `mail_template_type` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `mail_template_translation` (
              `mail_template_id` binary(16) NOT NULL,
              `language_id` binary(16) NOT NULL,
              `sender_name` varchar(255) DEFAULT NULL,
              `subject` varchar(255) DEFAULT NULL,
              `description` longtext DEFAULT NULL,
              `content_html` longtext DEFAULT NULL,
              `content_plain` longtext DEFAULT NULL,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`mail_template_id`,`language_id`),
              KEY `fk.mail_template_translation.language_id` (`language_id`),
              CONSTRAINT `fk.mail_template_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.mail_template_translation.mail_template_id` FOREIGN KEY (`mail_template_id`) REFERENCES `mail_template` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.mail_template_translation.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
