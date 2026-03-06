<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1536232750Snippet extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536232750;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `snippet_set` (
              `id` binary(16) NOT NULL,
              `name` varchar(255) NOT NULL,
              `base_file` varchar(255) NOT NULL,
              `iso` varchar(255) NOT NULL,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              CONSTRAINT `json.snippet_set.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `snippet` (
              `id` binary(16) NOT NULL,
              `translation_key` varchar(255) NOT NULL,
              `value` longtext NOT NULL,
              `author` varchar(255) NOT NULL,
              `snippet_set_id` binary(16) NOT NULL,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq.snippet_set_id_translation_key` (`snippet_set_id`,`translation_key`),
              CONSTRAINT `fk.snippet.snippet_set_id` FOREIGN KEY (`snippet_set_id`) REFERENCES `snippet_set` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.snippet.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
