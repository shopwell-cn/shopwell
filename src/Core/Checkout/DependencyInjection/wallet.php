<?php declare(strict_types=1);

use Doctrine\DBAL\Connection;
use Shopwell\Core\Checkout\Wallet\Aggregate\WalletCustomerWithdrawMethod\WalletCustomerWithdrawMethodEntity;
use Shopwell\Core\Checkout\Wallet\Aggregate\WalletFreeze\WalletFreezeEntity;
use Shopwell\Core\Checkout\Wallet\Aggregate\WalletTransaction\WalletTransactionEntity;
use Shopwell\Core\Checkout\Wallet\Aggregate\WalletWithdraw\WalletWithdrawEntity;
use Shopwell\Core\Checkout\Wallet\Aggregate\WalletWithdrawMethod\WalletWithdrawMethodEntity;
use Shopwell\Core\Checkout\Wallet\Subscriber\CustomerWalletSubscriber;
use Shopwell\Core\Checkout\Wallet\WalletEntity;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(WalletEntity::class)->tag('shopwell.entity');
    $services->set(WalletTransactionEntity::class)->tag('shopwell.entity');
    $services->set(WalletFreezeEntity::class)->tag('shopwell.entity');
    $services->set(WalletWithdrawEntity::class)->tag('shopwell.entity');
    $services->set(WalletWithdrawMethodEntity::class)->tag('shopwell.entity');
    $services->set(WalletCustomerWithdrawMethodEntity::class)->tag('shopwell.entity');

    $services->set(CustomerWalletSubscriber::class)
        ->args([
            service(Connection::class),
        ])
        ->tag('kernel.event_subscriber');
};
