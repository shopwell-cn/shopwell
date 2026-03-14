<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\InheritanceUpdaterTrait;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1574695657ProductCrossSelling extends MigrationStep
{
    use InheritanceUpdaterTrait;

    public function getCreationTimestamp(): int
    {
        return 1574695657;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `product_cross_selling` (
              `id` binary(16) NOT NULL,
              `type` varchar(255) NOT NULL,
              `position` int(11) NOT NULL,
              `sort_by` varchar(255) NOT NULL,
              `sort_direction` varchar(255) NOT NULL,
              `active` tinyint(1) DEFAULT 0,
              `limit` int(11) NOT NULL DEFAULT 24,
              `product_id` binary(16) NOT NULL,
              `product_version_id` binary(16) NOT NULL,
              `product_stream_id` binary(16) DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `fk.product_cross_selling.product_id` (`product_id`,`product_version_id`),
              KEY `fk.product_cross_selling.product_stream_id` (`product_stream_id`),
              CONSTRAINT `fk.product_cross_selling.product_id` FOREIGN KEY (`product_id`, `product_version_id`) REFERENCES `product` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.product_cross_selling.product_stream_id` FOREIGN KEY (`product_stream_id`) REFERENCES `product_stream` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
             CREATE TABLE `product_cross_selling_translation` (
              `product_cross_selling_id` binary(16) NOT NULL,
              `language_id` binary(16) NOT NULL,
              `name` varchar(255) DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`product_cross_selling_id`,`language_id`),
              KEY `fk.product_cross_selling_translation.language_id` (`language_id`),
              CONSTRAINT `fk.product_cross_selling_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.product_cross_selling_translation.product_cross_selling_id` FOREIGN KEY (`product_cross_selling_id`) REFERENCES `product_cross_selling` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // nth
    }
}
