<?php declare(strict_types=1);

use Doctrine\DBAL\Connection;
use Shopwell\Core\Finance\Wallet\Aggregate\WalletFreeze\WalletFreezeEntity;
use Shopwell\Core\Finance\Wallet\Aggregate\WalletTransaction\WalletTransactionEntity;
use Shopwell\Core\Finance\Wallet\Subscriber\CustomerWalletSubscriber;
use Shopwell\Core\Finance\Wallet\WalletEntity;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(WalletEntity::class)->tag('shopwell.entity');
    $services->set(WalletTransactionEntity::class)->tag('shopwell.entity');
    $services->set(WalletFreezeEntity::class)->tag('shopwell.entity');

    $services->set(CustomerWalletSubscriber::class)
        ->args([
            service(Connection::class),
        ])
        ->tag('kernel.event_subscriber');
};
