<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_5;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1669124190AddDoctrineMessengerTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1669124190;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement(
            '
            CREATE TABLE `messenger_messages` (
              `id` bigint(20) NOT NULL AUTO_INCREMENT,
              `body` longtext NOT NULL,
              `headers` longtext NOT NULL,
              `queue_name` varchar(190) NOT NULL,
              `created_at` datetime NOT NULL,
              `available_at` datetime NOT NULL,
              `delivered_at` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `queue_name` (`queue_name`),
              KEY `available_at` (`available_at`),
              KEY `delivered_at` (`delivered_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            '
        );
    }
}
