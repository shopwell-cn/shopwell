<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1578650334AddGoogleAnalyticsTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1578650334;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement(
            <<<'SQL'
             CREATE TABLE `sales_channel_analytics` (
              `id` binary(16) NOT NULL,
              `tracking_id` varchar(50) NOT NULL,
              `active` tinyint(1) NOT NULL DEFAULT 0,
              `track_orders` tinyint(1) NOT NULL DEFAULT 0,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `anonymize_ip` tinyint(1) NOT NULL DEFAULT 1,
              `track_offcanvas_cart` tinyint(1) NOT NULL DEFAULT 0,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
        );

        $connection->executeStatement(
            <<<'SQL'
            ALTER TABLE `sales_channel`
            ADD `analytics_id` binary(16) NULL AFTER `payment_method_ids`,
            ADD CONSTRAINT `fk.sales_channel.analytics_id` FOREIGN KEY (`analytics_id`) REFERENCES `sales_channel_analytics` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
SQL
        );
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
