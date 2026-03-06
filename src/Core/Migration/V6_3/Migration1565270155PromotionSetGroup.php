<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1565270155PromotionSetGroup extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1565270155;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `promotion_setgroup` (
              `id` binary(16) NOT NULL,
              `promotion_id` binary(16) NOT NULL,
              `packager_key` varchar(255) NOT NULL,
              `sorter_key` varchar(255) NOT NULL,
              `value` double NOT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `idx.promotion_setgroup.promotion_id` (`promotion_id`),
              CONSTRAINT `fk.promotion_setgroup.promotion_id` FOREIGN KEY (`promotion_id`) REFERENCES `promotion` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
