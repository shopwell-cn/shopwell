<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_5;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Checkout\Shipping\ShippingMethodDefinition;
use Shopwell\Core\Defaults;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;
use Shopwell\Core\Framework\Uuid\Uuid;

/**
 * @internal
 */
#[Package('framework')]
class Migration1692279790AppShippingMethod extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1692279790;
    }

    public function update(Connection $connection): void
    {
        $this->addAppShippingMethodTable($connection);

        $this->addDefaultMediaFolder($connection);
    }

    private function addAppShippingMethodTable(Connection $connection): void
    {
        $connection->executeStatement(
            <<<'SQL'
CREATE TABLE `app_shipping_method` (
  `id` binary(16) NOT NULL,
  `app_id` binary(16) DEFAULT NULL,
  `app_name` varchar(255) NOT NULL,
  `shipping_method_id` binary(16) NOT NULL,
  `original_media_id` binary(16) DEFAULT NULL,
  `identifier` varchar(255) NOT NULL,
  `created_at` datetime(3) NOT NULL,
  `updated_at` datetime(3) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq.app_shipping_method.shipping_method_id` (`shipping_method_id`),
  KEY `fk.app_shipping_method.app_id` (`app_id`),
  KEY `fk.app_shipping_method.original_media_id` (`original_media_id`),
  CONSTRAINT `fk.app_shipping_method.app_id` FOREIGN KEY (`app_id`) REFERENCES `app` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk.app_shipping_method.original_media_id` FOREIGN KEY (`original_media_id`) REFERENCES `media` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk.app_shipping_method.shipping_method_id` FOREIGN KEY (`shipping_method_id`) REFERENCES `shipping_method` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
        );
    }

    private function addDefaultMediaFolder(Connection $connection): void
    {
        $defaultFolderId = Uuid::randomBytes();
        $configurationId = Uuid::randomBytes();

        $connection->executeStatement(
            'REPLACE INTO `media_default_folder` SET
                id = :id,
                entity = :entity,
                created_at = :created_at;',
            [
                'id' => $defaultFolderId,
                'entity' => ShippingMethodDefinition::ENTITY_NAME,
                'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]
        );

        $connection->insert('media_folder_configuration', [
            'id' => $configurationId,
            'thumbnail_quality' => 80,
            'create_thumbnails' => 1,
            'private' => 0,
            'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        $connection->insert('media_folder', [
            'id' => Uuid::randomBytes(),
            'default_folder_id' => $defaultFolderId,
            'name' => 'Shipping Method Media',
            'media_folder_configuration_id' => $configurationId,
            'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
    }
}
