<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1536233210SalesChannelDomain extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536233210;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `sales_channel_domain` (
              `id` binary(16) NOT NULL,
              `sales_channel_id` binary(16) NOT NULL,
              `language_id` binary(16) NOT NULL,
              `url` varchar(255) NOT NULL,
              `currency_id` binary(16) NOT NULL,
              `snippet_set_id` binary(16) NOT NULL,
              `hreflang_use_only_locale` tinyint(1) unsigned DEFAULT 0,
              `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields`)),
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `measurement_units` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`measurement_units`)),
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq.sales_channel_domain.url` (`url`),
              KEY `fk.sales_channel_domain.currency_id` (`currency_id`),
              KEY `fk.sales_channel_domain.snippet_set_id` (`snippet_set_id`),
              KEY `fk.sales_channel_domain.language_id` (`language_id`),
              KEY `fk.sales_channel_domain.sales_channel_id` (`sales_channel_id`),
              CONSTRAINT `fk.sales_channel_domain.currency_id` FOREIGN KEY (`currency_id`) REFERENCES `currency` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `fk.sales_channel_domain.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `fk.sales_channel_domain.sales_channel_id` FOREIGN KEY (`sales_channel_id`) REFERENCES `sales_channel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.sales_channel_domain.snippet_set_id` FOREIGN KEY (`snippet_set_id`) REFERENCES `snippet_set` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `json.sales_channel_domain.custom_fields` CHECK (json_valid(`custom_fields`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
