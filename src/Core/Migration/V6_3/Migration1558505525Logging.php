<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Defaults;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;
use Shopwell\Core\Framework\Uuid\Uuid;

/**
 * @internal
 */
#[Package('framework')]
class Migration1558505525Logging extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1558505525;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `log_entry` (
              `id` binary(16) NOT NULL,
              `message` longtext NOT NULL,
              `level` smallint(6) NOT NULL,
              `channel` varchar(255) NOT NULL,
              `context` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`context`)),
              `extra` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`extra`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `idx.log_entry.created_at` (`created_at`),
              CONSTRAINT `json.log_entry.context` CHECK (json_valid(`context`)),
              CONSTRAINT `json.log_entry.extra` CHECK (json_valid(`extra`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->insert('system_config', [
            'id' => Uuid::randomBytes(),
            'configuration_key' => 'core.logging.cleanupInterval',
            'configuration_value' => '{"_value": "86400"}',
            'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        $connection->insert('system_config', [
            'id' => Uuid::randomBytes(),
            'configuration_key' => 'core.logging.entryLimit',
            'configuration_value' => '{"_value": "10000000"}',
            'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        $connection->insert('system_config', [
            'id' => Uuid::randomBytes(),
            'configuration_key' => 'core.logging.entryLifetimeSeconds',
            'configuration_value' => '{"_value": "2678400"}', // one month
            'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
