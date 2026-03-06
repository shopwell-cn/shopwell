<?php declare(strict_types=1);

namespace Shopwell\Core\Installer\Configuration;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Installer\Controller\ShopConfigurationController;
use Shopwell\Core\Maintenance\User\Service\UserProvisioner;

/**
 * @internal
 *
 * @phpstan-import-type AdminUser from ShopConfigurationController
 */
#[Package('framework')]
class AdminConfigurationService
{
    /**
     * @param AdminUser $user
     */
    public function createAdmin(array $user, Connection $connection): void
    {
        $userProvisioner = new UserProvisioner($connection);
        $userProvisioner->provision(
            $user['username'],
            $user['password'],
            [
                'firstName' => $user['firstName'],
                'lastName' => $user['lastName'],
                'email' => $user['email'],
            ]
        );
    }
}
