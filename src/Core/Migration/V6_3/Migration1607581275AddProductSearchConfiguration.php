<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;
use Shopwell\Core\Migration\Traits\ImportTranslationsTrait;

/**
 * @internal
 */
#[Package('framework')]
class Migration1607581275AddProductSearchConfiguration extends MigrationStep
{
    use ImportTranslationsTrait;

    public function getCreationTimestamp(): int
    {
        return 1607581275;
    }

    public function update(Connection $connection): void
    {
        $this->createProductSearchConfigTable($connection);
        $this->createProductSearchConfigFieldTable($connection);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }

    private function createProductSearchConfigTable(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `product_search_config` (
              `id` binary(16) NOT NULL,
              `language_id` binary(16) NOT NULL,
              `and_logic` tinyint(1) NOT NULL DEFAULT 1,
              `min_search_length` smallint(6) NOT NULL DEFAULT 2,
              `excluded_terms` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`excluded_terms`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq.product_search_config.language_id` (`language_id`),
              CONSTRAINT `fk.product_search_config.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `json.product_search_config.excluded_terms` CHECK (json_valid(`excluded_terms`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    private function createProductSearchConfigFieldTable(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `product_search_config_field` (
              `id` binary(16) NOT NULL,
              `product_search_config_id` binary(16) NOT NULL,
              `custom_field_id` binary(16) DEFAULT NULL,
              `field` varchar(255) NOT NULL,
              `tokenize` tinyint(1) NOT NULL DEFAULT 0,
              `searchable` tinyint(1) NOT NULL DEFAULT 0,
              `ranking` int(11) NOT NULL DEFAULT 0,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq.search_config_field.field__config_id` (`field`,`product_search_config_id`),
              KEY `fk.search_config_field.product_search_config_id` (`product_search_config_id`),
              KEY `fk.search_config_field.custom_field_id` (`custom_field_id`),
              CONSTRAINT `fk.search_config_field.custom_field_id` FOREIGN KEY (`custom_field_id`) REFERENCES `custom_field` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.search_config_field.product_search_config_id` FOREIGN KEY (`product_search_config_id`) REFERENCES `product_search_config` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }
}
