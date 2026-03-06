<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_4;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1621845357AddFlow extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1621845357;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `flow` (
              `id` binary(16) NOT NULL,
              `name` varchar(255) NOT NULL,
              `description` mediumtext DEFAULT NULL,
              `event_name` varchar(255) NOT NULL,
              `priority` int(11) NOT NULL DEFAULT 1,
              `payload` longblob DEFAULT NULL,
              `invalid` tinyint(1) NOT NULL DEFAULT 0,
              `active` tinyint(1) NOT NULL DEFAULT 0,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `idx.flow.event_name` (`event_name`,`priority`),
              CONSTRAINT `json.flow.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
