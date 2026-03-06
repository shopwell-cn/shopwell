<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1536233360Document extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536233360;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<'SQL'
            CREATE TABLE `document_type` (
              `id` binary(16) NOT NULL,
              `technical_name` varchar(255) NOT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq.document_type.technical_name` (`technical_name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<'SQL'
            CREATE TABLE `document_type_translation` (
              `document_type_id` binary(16) NOT NULL,
              `language_id` binary(16) NOT NULL,
              `name` varchar(255) DEFAULT NULL,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`document_type_id`,`language_id`),
              KEY `fk.document_type_translation.language_id` (`language_id`),
              CONSTRAINT `fk.document_type_translation.document_type_id` FOREIGN KEY (`document_type_id`) REFERENCES `document_type` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.document_type_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.document_type_translation.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<'SQL'
            CREATE TABLE `document` (
              `id` binary(16) NOT NULL,
              `document_type_id` binary(16) NOT NULL,
              `referenced_document_id` binary(16) DEFAULT NULL,
              `order_id` binary(16) NOT NULL,
              `order_version_id` binary(16) NOT NULL,
              `config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`config`)),
              `sent` tinyint(1) NOT NULL DEFAULT 0,
              `static` tinyint(1) NOT NULL DEFAULT 0,
              `deep_link_code` varchar(32) NOT NULL,
              `document_media_file_id` binary(16) DEFAULT NULL,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `document_number` varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`config`,'$.documentNumber'))) STORED,
              `document_a11y_media_file_id` binary(16) DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq.deep_link_code` (`deep_link_code`),
              KEY `fk.document.document_type_id` (`document_type_id`),
              KEY `fk.document.referenced_document_id` (`referenced_document_id`),
              KEY `fk.document.order_id` (`order_id`,`order_version_id`),
              KEY `fk.document.document_media_file_id` (`document_media_file_id`),
              KEY `idx.document.document_number` (`document_number`),
              KEY `fk.document.document_a11y_media_file_id` (`document_a11y_media_file_id`),
              CONSTRAINT `fk.document.document_a11y_media_file_id` FOREIGN KEY (`document_a11y_media_file_id`) REFERENCES `media` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `fk.document.document_media_file_id` FOREIGN KEY (`document_media_file_id`) REFERENCES `media` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `fk.document.document_type_id` FOREIGN KEY (`document_type_id`) REFERENCES `document_type` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `fk.document.order_id` FOREIGN KEY (`order_id`, `order_version_id`) REFERENCES `order` (`id`, `version_id`) ON UPDATE CASCADE,
              CONSTRAINT `fk.document.referenced_document_id` FOREIGN KEY (`referenced_document_id`) REFERENCES `document` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `json.document.custom_fields` CHECK (json_valid(`custom_fields`)),
              CONSTRAINT `json.document.config` CHECK (json_valid(`config`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
