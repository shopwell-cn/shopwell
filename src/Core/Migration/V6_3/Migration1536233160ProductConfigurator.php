<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1536233160ProductConfigurator extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536233160;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `product_configurator_setting` (
              `id` binary(16) NOT NULL,
              `version_id` binary(16) NOT NULL,
              `product_id` binary(16) NOT NULL,
              `product_version_id` binary(16) NOT NULL,
              `property_group_option_id` binary(16) NOT NULL,
              `price` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`price`)),
              `position` int(11) NOT NULL DEFAULT 0,
              `media_id` binary(16) DEFAULT NULL,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`,`version_id`),
              UNIQUE KEY `uniq.product_configurator_setting.prod_id.vers_id.prop_group_id` (`product_id`,`version_id`,`property_group_option_id`),
              KEY `fk.product_configurator_setting.product_id` (`product_id`,`product_version_id`),
              KEY `fk.product_configurator_setting.media_id` (`media_id`),
              KEY `fk.product_configurator_setting.property_group_option_id` (`property_group_option_id`),
              CONSTRAINT `fk.product_configurator_setting.media_id` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE SET NULL,
              CONSTRAINT `fk.product_configurator_setting.product_id` FOREIGN KEY (`product_id`, `product_version_id`) REFERENCES `product` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.product_configurator_setting.property_group_option_id` FOREIGN KEY (`property_group_option_id`) REFERENCES `property_group_option` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `json.product_configurator_setting.price` CHECK (json_valid(`price`)),
              CONSTRAINT `json.product_configurator_setting.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
