<?php declare(strict_types=1);

use Shopwell\Core\Payment\Order\PaymentOrderEntity;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(PaymentOrderEntity::class)->tag('shopwell.entity');
};
