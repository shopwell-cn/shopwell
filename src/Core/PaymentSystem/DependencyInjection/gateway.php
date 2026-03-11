<?php declare(strict_types=1);

use Shopwell\Core\PaymentSystem\Gateway\DataAbstractionLayer\GatewayConfigEntity;
use Shopwell\Core\PaymentSystem\Gateway\DataAbstractionLayer\PaymentTokenEntity;
use Shopwell\Core\PaymentSystem\Gateway\PaymentTypeRegistry;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

return function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(PaymentTypeRegistry::class)
        ->args([
            param('shopwell.payment.allowed_types'),
        ]);

    $services->set(GatewayConfigEntity::class)->tag('shopwell.entity');
    $services->set(PaymentTokenEntity::class)->tag('shopwell.entity');
};
