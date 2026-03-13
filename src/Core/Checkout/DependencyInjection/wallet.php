<?php declare(strict_types=1);

use Shopwell\Core\Checkout\Wallet\WalletEntity;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(WalletEntity::class)->tag('shopwell.entity');
};
