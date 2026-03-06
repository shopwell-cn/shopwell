<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1597762808Webhook extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1597762808;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `webhook` (
              `id` binary(16) NOT NULL,
              `name` varchar(255) NOT NULL,
              `event_name` varchar(500) NOT NULL,
              `url` varchar(500) NOT NULL,
              `app_id` binary(16) DEFAULT NULL,
              `active` tinyint(1) DEFAULT 1,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `error_count` int(11) NOT NULL DEFAULT 0,
              `only_live_version` tinyint(1) unsigned NOT NULL DEFAULT 0,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq.webhook.name` (`name`,`app_id`),
              KEY `fk.webhook.app_id` (`app_id`),
              CONSTRAINT `fk.webhook.app_id` FOREIGN KEY (`app_id`) REFERENCES `app` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // nth
    }
}
