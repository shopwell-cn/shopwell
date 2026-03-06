<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1536233190ProductPriceRule extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536233190;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `product_price` (
              `id` binary(16) NOT NULL,
              `version_id` binary(16) NOT NULL,
              `rule_id` binary(16) NOT NULL,
              `product_id` binary(16) NOT NULL,
              `product_version_id` binary(16) NOT NULL,
              `price` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`price`)),
              `quantity_start` int(11) NOT NULL,
              `quantity_end` int(11) DEFAULT NULL,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`,`version_id`),
              KEY `fk.product_price.product_id` (`product_id`,`product_version_id`),
              KEY `fk.product_price.rule_id` (`rule_id`),
              CONSTRAINT `fk.product_price.product_id` FOREIGN KEY (`product_id`, `product_version_id`) REFERENCES `product` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.product_price.rule_id` FOREIGN KEY (`rule_id`) REFERENCES `rule` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `json.product_price.price` CHECK (json_valid(`price`)),
              CONSTRAINT `json.product_price.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
