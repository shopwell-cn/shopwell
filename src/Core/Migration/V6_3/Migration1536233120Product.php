<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1536233120Product extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536233120;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `product` (
              `id` binary(16) NOT NULL,
              `version_id` binary(16) NOT NULL,
              `auto_increment` int(11) NOT NULL AUTO_INCREMENT,
              `product_number` varchar(64) DEFAULT NULL,
              `active` tinyint(3) unsigned DEFAULT NULL,
              `parent_id` binary(16) DEFAULT NULL,
              `parent_version_id` binary(16) DEFAULT NULL,
              `tax_id` binary(16) DEFAULT NULL,
              `product_manufacturer_id` binary(16) DEFAULT NULL,
              `product_manufacturer_version_id` binary(16) DEFAULT NULL,
              `delivery_time_id` binary(16) DEFAULT NULL,
              `deliveryTime` binary(16) DEFAULT NULL,
              `product_media_id` binary(16) DEFAULT NULL,
              `product_media_version_id` binary(16) DEFAULT NULL,
              `unit_id` binary(16) DEFAULT NULL,
              `category_tree` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`category_tree`)),
              `category_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`category_ids`)),
              `stream_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`stream_ids`)),
              `option_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`option_ids`)),
              `property_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`property_ids`)),
              `tax` binary(16) DEFAULT NULL,
              `manufacturer` binary(16) DEFAULT NULL,
              `cover` binary(16) DEFAULT NULL,
              `unit` binary(16) DEFAULT NULL,
              `media` binary(16) DEFAULT NULL,
              `prices` binary(16) DEFAULT NULL,
              `visibilities` binary(16) DEFAULT NULL,
              `properties` binary(16) DEFAULT NULL,
              `categories` binary(16) DEFAULT NULL,
              `translations` binary(16) DEFAULT NULL,
              `price` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`price`)),
              `manufacturer_number` varchar(255) DEFAULT NULL,
              `ean` varchar(255) DEFAULT NULL,
              `sales` int(11) NOT NULL DEFAULT 0,
              `stock` int(11) NOT NULL,
              `available_stock` int(11) DEFAULT NULL,
              `available` tinyint(1) NOT NULL DEFAULT 0,
              `restock_time` int(11) DEFAULT NULL,
              `is_closeout` tinyint(1) DEFAULT NULL,
              `purchase_steps` int(11) unsigned DEFAULT NULL,
              `max_purchase` int(11) unsigned DEFAULT NULL,
              `min_purchase` int(11) unsigned DEFAULT NULL,
              `purchase_unit` decimal(11,4) unsigned DEFAULT NULL,
              `reference_unit` decimal(10,3) unsigned DEFAULT NULL,
              `shipping_free` tinyint(1) DEFAULT NULL,
              `purchase_prices` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`purchase_prices`)),
              `mark_as_topseller` tinyint(1) unsigned DEFAULT NULL,
              `weight` decimal(15,6) unsigned DEFAULT NULL,
              `width` decimal(10,3) unsigned DEFAULT NULL,
              `height` decimal(10,3) unsigned DEFAULT NULL,
              `length` decimal(10,3) unsigned DEFAULT NULL,
              `release_date` datetime(3) DEFAULT NULL,
              `tag_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tag_ids`)),
              `tags` binary(16) DEFAULT NULL,
              `variant_restrictions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`variant_restrictions`)),
              `created_at` datetime(3) DEFAULT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `rating_average` float DEFAULT NULL,
              `display_group` varchar(50) DEFAULT NULL,
              `child_count` int(11) DEFAULT NULL,
              `crossSellings` binary(16) DEFAULT NULL,
              `featureSet` binary(16) DEFAULT NULL,
              `customFieldSets` binary(16) DEFAULT NULL,
              `custom_field_set_selection_active` tinyint(1) DEFAULT NULL,
              `canonical_product_id` binary(16) DEFAULT NULL,
              `canonical_product_version_id` binary(16) DEFAULT NULL,
              `canonicalProduct` binary(16) DEFAULT NULL,
              `cheapest_price` longtext DEFAULT NULL,
              `cheapest_price_accessor` longtext DEFAULT NULL,
              `states` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`states`)),
              `variant_listing_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`variant_listing_config`)),
              `type` varchar(32) NOT NULL DEFAULT \'physical\',
              `mainCategories` binary(16) DEFAULT NULL,
              PRIMARY KEY (`id`,`version_id`),
              UNIQUE KEY `auto_increment` (`auto_increment`),
              UNIQUE KEY `uniq.product.product_number__version_id` (`product_number`,`version_id`),
              KEY `fk.product.tax_id` (`tax_id`),
              KEY `fk.product.unit_id` (`unit_id`),
              KEY `fk.product.parent_id` (`parent_id`,`parent_version_id`),
              KEY `fk.product.product_media_id` (`product_media_id`,`product_media_version_id`),
              KEY `fk.product.product_manufacturer` (`product_manufacturer_id`,`product_manufacturer_version_id`),
              KEY `fk.product.canonical_product_id` (`canonical_product_id`,`canonical_product_version_id`),
              KEY `idx.product.categories` (`categories`),
              KEY `idx.product.type` (`type`),
              CONSTRAINT `fk.product.canonical_product_id` FOREIGN KEY (`canonical_product_id`, `canonical_product_version_id`) REFERENCES `product` (`id`, `version_id`) ON DELETE SET NULL,
              CONSTRAINT `fk.product.parent_id` FOREIGN KEY (`parent_id`, `parent_version_id`) REFERENCES `product` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.product.product_manufacturer` FOREIGN KEY (`product_manufacturer_id`, `product_manufacturer_version_id`) REFERENCES `product_manufacturer` (`id`, `version_id`) ON DELETE SET NULL ON UPDATE CASCADE,
              CONSTRAINT `fk.product.tax_id` FOREIGN KEY (`tax_id`) REFERENCES `tax` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `fk.product.unit_id` FOREIGN KEY (`unit_id`) REFERENCES `unit` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `json.product.category_tree` CHECK (json_valid(`category_tree`)),
              CONSTRAINT `json.product.option_ids` CHECK (json_valid(`option_ids`)),
              CONSTRAINT `json.product.property_ids` CHECK (json_valid(`property_ids`)),
              CONSTRAINT `json.product.price` CHECK (json_valid(`price`)),
              CONSTRAINT `json.product.tag_ids` CHECK (json_valid(`tag_ids`)),
              CONSTRAINT `json.product.variant_restrictions` CHECK (json_valid(`variant_restrictions`)),
              CONSTRAINT `json.product.states` CHECK (json_valid(`states`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE `product_translation` (
              `product_id` binary(16) NOT NULL,
              `product_version_id` binary(16) NOT NULL,
              `language_id` binary(16) NOT NULL,
              `meta_description` varchar(255) DEFAULT NULL,
              `name` varchar(255) DEFAULT NULL,
              `keywords` mediumtext DEFAULT NULL,
              `description` mediumtext DEFAULT NULL,
              `meta_title` varchar(255) DEFAULT NULL,
              `pack_unit` varchar(255) DEFAULT NULL,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `slot_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`slot_config`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `pack_unit_plural` varchar(255) DEFAULT NULL,
              `custom_search_keywords` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_search_keywords`)),
              PRIMARY KEY (`product_id`,`product_version_id`,`language_id`),
              KEY `fk.product_translation.language_id` (`language_id`),
              CONSTRAINT `fk.product_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.product_translation.product_id` FOREIGN KEY (`product_id`, `product_version_id`) REFERENCES `product` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.product_translation.custom_fields` CHECK (json_valid(`custom_fields`)),
              CONSTRAINT `json.product_translation.custom_search_keywords` CHECK (json_valid(`custom_search_keywords`)),
              CONSTRAINT `json.product_translation.slot_config` CHECK (json_valid(`slot_config`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
