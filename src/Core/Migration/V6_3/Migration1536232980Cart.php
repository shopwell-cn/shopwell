<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1536232980Cart extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536232980;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `cart` (
              `token` varchar(50) NOT NULL,
              `rule_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`rule_ids`)),
              `created_at` datetime(3) NOT NULL,
              `auto_increment` bigint(20) NOT NULL AUTO_INCREMENT,
              `compressed` tinyint(1) NOT NULL DEFAULT 0,
              `payload` longblob DEFAULT NULL,
              PRIMARY KEY (`token`),
              UNIQUE KEY `auto_increment` (`auto_increment`),
              KEY `idx.cart.created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
