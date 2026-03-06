<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_7;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1742568836CreateThemeRuntimeConfigTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1742568836;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement(<<<'SQL'
            CREATE TABLE `theme_runtime_config` (
              `theme_id` binary(16) NOT NULL,
              `technical_name` varchar(255) DEFAULT NULL,
              `resolved_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`resolved_config`)),
              `view_inheritance` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`view_inheritance`)),
              `script_files` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`script_files`)),
              `icon_sets` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`icon_sets`)),
              `updated_at` datetime(3) NOT NULL,
              PRIMARY KEY (`theme_id`),
              UNIQUE KEY `uidx.technical_name` (`technical_name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }
}
