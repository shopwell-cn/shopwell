<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_4;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Checkout\Payment\PaymentMethodDefinition;
use Shopwell\Core\Defaults;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;
use Shopwell\Core\Framework\Uuid\Uuid;

/**
 * @internal
 */
#[Package('framework')]
class Migration1616076922AppPaymentMethod extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1616076922;
    }

    public function update(Connection $connection): void
    {
        $this->addAppPaymentMethod($connection);
        $this->addDefaultMediaFolder($connection);
    }

    public function updateDestructive(Connection $connection): void
    {
    }

    private function addAppPaymentMethod(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `app_payment_method` (
              `id` binary(16) NOT NULL,
              `app_id` binary(16) DEFAULT NULL,
              `payment_method_id` binary(16) NOT NULL,
              `app_name` varchar(255) NOT NULL,
              `identifier` varchar(255) NOT NULL,
              `pay_url` varchar(255) DEFAULT NULL,
              `finalize_url` varchar(255) DEFAULT NULL,
              `validate_url` varchar(255) DEFAULT NULL,
              `capture_url` varchar(255) DEFAULT NULL,
              `refund_url` varchar(255) DEFAULT NULL,
              `original_media_id` binary(16) DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `recurring_url` varchar(255) DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq.app_payment_method.payment_method_id` (`payment_method_id`),
              KEY `fk.app_payment_method.app_id` (`app_id`),
              KEY `fk.app_payment_method.original_media_id` (`original_media_id`),
              CONSTRAINT `fk.app_payment_method.app_id` FOREIGN KEY (`app_id`) REFERENCES `app` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
              CONSTRAINT `fk.app_payment_method.original_media_id` FOREIGN KEY (`original_media_id`) REFERENCES `media` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
              CONSTRAINT `fk.app_payment_method.payment_method_id` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_method` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
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
                'entity' => PaymentMethodDefinition::ENTITY_NAME,
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
            'name' => 'Payment Method Media',
            'media_folder_configuration_id' => $configurationId,
            'created_at' => new \DateTime()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
    }
}
