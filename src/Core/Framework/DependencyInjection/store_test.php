<?php declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use Shopwell\Core\Framework\Store\Services\InstanceService;
use Shopwell\Core\Kernel;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('shopwell.store.mock_handler', MockHandler::class)
        ->public();

    $services->set('shopwell.store_client', Client::class)
        ->public()
        ->args([['handler' => inline_service(HandlerStack::class)
            ->args([service('shopwell.store.mock_handler')])
            ->factory([HandlerStack::class, 'create'])]]);

    $services->set('shopwell.frw.mock_handler', MockHandler::class)
        ->public();

    $services->set('shopwell.frw_client', Client::class)
        ->public()
        ->args([['handler' => inline_service(HandlerStack::class)
            ->args([service('shopwell.frw.mock_handler')])
            ->factory([HandlerStack::class, 'create'])]]);

    $services->set('shopwell.store_download_client', Client::class)
        ->args([['handler' => inline_service(HandlerStack::class)
            ->args([service('shopwell.store.mock_handler')])
            ->factory([HandlerStack::class, 'create'])]]);

    $services->set(InstanceService::class)
        ->args([
            Kernel::SHOPWELL_FALLBACK_VERSION,
            'this-is-a-unique-id',
        ]);
};
