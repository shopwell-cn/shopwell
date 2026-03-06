<?php declare(strict_types=1);

namespace Shopwell\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('framework')]
class Migration1536233260StateMachineHistory extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536233260;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<'SQL'
            CREATE TABLE `state_machine_history` (
              `id` binary(16) NOT NULL,
              `state_machine_id` binary(16) NOT NULL,
              `entity_name` varchar(100) NOT NULL,
              `from_state_id` binary(16) NOT NULL,
              `to_state_id` binary(16) NOT NULL,
              `action_name` varchar(255) NOT NULL,
              `user_id` binary(16) DEFAULT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `referenced_id` binary(16) NOT NULL,
              `referenced_version_id` binary(16) NOT NULL,
              `integration_id` binary(16) DEFAULT NULL,
              `internal_comment` text DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `fk.state_machine_history.state_machine_id` (`state_machine_id`),
              KEY `fk.state_machine_history.from_state_id` (`from_state_id`),
              KEY `fk.state_machine_history.to_state_id` (`to_state_id`),
              KEY `fk.state_machine_history.user_id` (`user_id`),
              KEY `idx.state_machine_history.referenced_entity` (`referenced_id`,`referenced_version_id`),
              KEY `fk.state_machine_history.integration_id` (`integration_id`),
              CONSTRAINT `fk.state_machine_history.from_state_id` FOREIGN KEY (`from_state_id`) REFERENCES `state_machine_state` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
              CONSTRAINT `fk.state_machine_history.integration_id` FOREIGN KEY (`integration_id`) REFERENCES `integration` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
              CONSTRAINT `fk.state_machine_history.state_machine_id` FOREIGN KEY (`state_machine_id`) REFERENCES `state_machine` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
              CONSTRAINT `fk.state_machine_history.to_state_id` FOREIGN KEY (`to_state_id`) REFERENCES `state_machine_state` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
              CONSTRAINT `fk.state_machine_history.user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
