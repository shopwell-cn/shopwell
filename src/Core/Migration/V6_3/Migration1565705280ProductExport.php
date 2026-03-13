<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Defaults;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;
use Shopwell\Core\Framework\Uuid\Uuid;

/**
 * @internal
 */
#[Package('framework')]
class Migration1565705280ProductExport extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1565705280;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `product_export` (
              `id` binary(16) NOT NULL,
              `product_stream_id` binary(16) NOT NULL,
              `storefront_sales_channel_id` binary(16) DEFAULT NULL,
              `sales_channel_id` binary(16) NOT NULL,
              `sales_channel_domain_id` binary(16) DEFAULT NULL,
              `file_name` varchar(255) NOT NULL,
              `access_key` varchar(255) NOT NULL,
              `encoding` varchar(255) NOT NULL,
              `file_format` varchar(255) NOT NULL,
              `include_variants` tinyint(1) DEFAULT 0,
              `generate_by_cronjob` tinyint(1) NOT NULL DEFAULT 0,
              `generated_at` datetime(3) DEFAULT NULL,
              `interval` int(11) NOT NULL,
              `header_template` longtext DEFAULT NULL,
              `body_template` longtext DEFAULT NULL,
              `footer_template` longtext DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `currency_id` binary(16) NOT NULL,
              `paused_schedule` tinyint(1) DEFAULT 0,
              `is_running` tinyint(1) NOT NULL DEFAULT 0,
              PRIMARY KEY (`id`),
              UNIQUE KEY `file_name` (`file_name`),
              KEY `fk.product_export.product_stream_id` (`product_stream_id`),
              KEY `fk.product_export.storefront_sales_channel_id` (`storefront_sales_channel_id`),
              KEY `fk.product_export.sales_channel_id` (`sales_channel_id`),
              KEY `fk.product_export.sales_channel_domain_id` (`sales_channel_domain_id`),
              CONSTRAINT `fk.product_export.product_stream_id` FOREIGN KEY (`product_stream_id`) REFERENCES `product_stream` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
              CONSTRAINT `fk.product_export.sales_channel_domain_id` FOREIGN KEY (`sales_channel_domain_id`) REFERENCES `sales_channel_domain` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `fk.product_export.sales_channel_id` FOREIGN KEY (`sales_channel_id`) REFERENCES `sales_channel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.product_export.storefront_sales_channel_id` FOREIGN KEY (`storefront_sales_channel_id`) REFERENCES `sales_channel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $this->createSalesChannelType($connection);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }

    private function createSalesChannelType(Connection $connection): void
    {
        $salesChannelTypeId = Uuid::fromHexToBytes('ed535e5722134ac1aa6524f73e26881b');

        $languageEN = Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM);
        $languageDE = $this->getDeDeLanguageId($connection);

        $connection->insert(
            'sales_channel_type',
            [
                'id' => $salesChannelTypeId,
                'icon_name' => 'default-object-rocket',
                'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]
        );
        $connection->insert(
            'sales_channel_type_translation',
            [
                'sales_channel_type_id' => $salesChannelTypeId,
                'language_id' => $languageEN,
                'name' => 'Product comparison',
                'manufacturer' => 'shopware AG',
                'description' => 'Sales channel for product comparison platforms',
                'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]
        );
        $connection->insert(
            'sales_channel_type_translation',
            [
                'sales_channel_type_id' => $salesChannelTypeId,
                'language_id' => $languageDE,
                'name' => 'Produktvergleich',
                'manufacturer' => 'shopware AG',
                'description' => 'Verkaufskanal für Produktvergleichsportale',
                'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]
        );
    }

    private function getDeDeLanguageId(Connection $connection): string
    {
        return (string) $connection->fetchOne(
            'SELECT id FROM language WHERE id != :default',
            ['default' => Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM)]
        );
    }
}
