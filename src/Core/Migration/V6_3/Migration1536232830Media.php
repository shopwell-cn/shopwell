<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1536232830Media extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536232830;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `media_default_folder` (
              `id` binary(16) NOT NULL,
              `entity` varchar(255) NOT NULL,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq.media_default_folder.entity` (`entity`),
              CONSTRAINT `json.media_default_folder.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `media_folder` (
              `id` binary(16) NOT NULL,
              `parent_id` binary(16) DEFAULT NULL,
              `default_folder_id` binary(16) DEFAULT NULL,
              `name` varchar(255) DEFAULT NULL,
              `child_count` int(11) unsigned NOT NULL DEFAULT 0,
              `path` longtext DEFAULT NULL,
              `media_folder_configuration_id` binary(16) DEFAULT NULL,
              `use_parent_configuration` tinyint(1) DEFAULT 1,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq.media_folder.default_folder_id` (`default_folder_id`),
              KEY `fk.media_folder.parent_id` (`parent_id`),
              CONSTRAINT `fk.media_folder.default_folder_id` FOREIGN KEY (`default_folder_id`) REFERENCES `media_default_folder` (`id`) ON DELETE SET NULL,
              CONSTRAINT `fk.media_folder.parent_id` FOREIGN KEY (`parent_id`) REFERENCES `media_folder` (`id`) ON DELETE CASCADE,
              CONSTRAINT `json.media_folder.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `media` (
              `id` binary(16) NOT NULL,
              `user_id` binary(16) DEFAULT NULL,
              `media_folder_id` binary(16) DEFAULT NULL,
              `mime_type` varchar(255) DEFAULT NULL,
              `file_extension` varchar(50) DEFAULT NULL,
              `file_size` int(10) unsigned DEFAULT NULL,
              `meta_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta_data`)),
              `file_name` longtext DEFAULT NULL,
              `media_type` longblob DEFAULT NULL,
              `thumbnails_ro` longblob DEFAULT NULL,
              `private` tinyint(1) NOT NULL DEFAULT 0,
              `uploaded_at` datetime(3) DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `path` varchar(2048) DEFAULT NULL,
              `config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`config`)),
              `file_hash` varchar(32) GENERATED ALWAYS AS (json_unquote(json_extract(`meta_data`,\'$.hash\'))) STORED,
              PRIMARY KEY (`id`),
              KEY `fk.media.user_id` (`user_id`),
              KEY `fk.media.media_folder_id` (`media_folder_id`),
              KEY `idx.media.file_extension` (`file_extension`),
              KEY `idx.media.file_name` (`file_name`(768)),
              KEY `idx.media.file_hash` (`file_hash`),
              KEY `idx.media.uploaded_at_created_at_id` (`uploaded_at`,`created_at`,`id`),
              CONSTRAINT `fk.media.media_folder_id` FOREIGN KEY (`media_folder_id`) REFERENCES `media_folder` (`id`) ON DELETE SET NULL,
              CONSTRAINT `fk.media.user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
              CONSTRAINT `json.media.meta_data` CHECK (json_valid(`meta_data`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `media_translation` (
              `media_id` binary(16) NOT NULL,
              `language_id` binary(16) NOT NULL,
              `alt` varchar(255) DEFAULT NULL,
              `title` varchar(255) DEFAULT NULL,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`media_id`,`language_id`),
              KEY `fk.media_translation.language_id` (`language_id`),
              CONSTRAINT `fk.media_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.media_translation.media_id` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.media_translation.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
             CREATE TABLE `media_folder_configuration` (
              `id` binary(16) NOT NULL,
              `create_thumbnails` tinyint(1) DEFAULT 1,
              `thumbnail_quality` int(11) DEFAULT 80,
              `media_thumbnail_sizes_ro` longblob DEFAULT NULL,
              `keep_aspect_ratio` tinyint(1) DEFAULT 1,
              `private` tinyint(1) DEFAULT 0,
              `no_association` tinyint(1) DEFAULT NULL,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              CONSTRAINT `json.media_folder_configuration.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `media_thumbnail_size` (
              `id` binary(16) NOT NULL,
              `width` int(11) NOT NULL,
              `height` int(11) NOT NULL,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq.width` (`width`,`height`),
              CONSTRAINT `json.media_thumbnail_size.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `media_folder_configuration_media_thumbnail_size` (
              `media_folder_configuration_id` binary(16) NOT NULL,
              `media_thumbnail_size_id` binary(16) NOT NULL,
              PRIMARY KEY (`media_folder_configuration_id`,`media_thumbnail_size_id`),
              KEY `fk.media_folder_configuration_media_thumbnail_size.size_id` (`media_thumbnail_size_id`),
              CONSTRAINT `fk.media_folder_configuration_media_thumbnail_size.conf_id` FOREIGN KEY (`media_folder_configuration_id`) REFERENCES `media_folder_configuration` (`id`) ON DELETE CASCADE,
              CONSTRAINT `fk.media_folder_configuration_media_thumbnail_size.size_id` FOREIGN KEY (`media_thumbnail_size_id`) REFERENCES `media_thumbnail_size` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            ALTER TABLE `user`
              ADD CONSTRAINT `fk.user.avatar_id` FOREIGN KEY (avatar_id)
                REFERENCES `media` (id) ON DELETE SET NULL;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // no destructive changes
    }
}
