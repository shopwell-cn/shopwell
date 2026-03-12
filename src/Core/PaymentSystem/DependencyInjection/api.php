<?php declare(strict_types=1);

use Shopwell\Core\Framework\Extensions\ExtensionDispatcher;
use Shopwell\Core\Framework\Validation\DataValidator;
use Shopwell\Core\PaymentSystem\Api\Handler\PaymentOrderConverter;
use Shopwell\Core\PaymentSystem\Api\Handler\PaymentOrderHandler;
use Shopwell\Core\System\NumberRange\ValueGenerator\NumberRangeValueGeneratorInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(PaymentOrderConverter::class)
        ->args([
            service('event_dispatcher'),
            service(NumberRangeValueGeneratorInterface::class),
        ]);

    $services->set(PaymentOrderHandler::class)
        ->args([
            service(DataValidator::class),
            service(PaymentOrderConverter::class),
            service('payment_order.repository'),
            service(ExtensionDispatcher::class),
        ]);
};
