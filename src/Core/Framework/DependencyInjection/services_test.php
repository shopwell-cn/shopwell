<?php declare(strict_types=1);

use Doctrine\DBAL\Connection;
use GuzzleHttp\Handler\MockHandler;
use Monolog\Handler\NullHandler;
use Shopwell\Core\Framework\App\Payment\Handler\AppPaymentHandler;
use Shopwell\Core\Framework\App\Payment\Payload\PaymentPayloadService;
use Shopwell\Core\Framework\Test\Filesystem\Adapter\MemoryAdapterFactory;
use Shopwell\Core\Framework\Test\TestCaseHelper\TestBrowser;
use Shopwell\Core\System\StateMachine\StateMachineRegistry;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Messenger\TraceableMessageBus;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $container->extension('monolog', [
        'handlers' => [
            'business_event_handler_discard' => [
                'type' => 'service',
                'id' => NullHandler::class,
                'priority' => 1,
                'channels' => ['business_events'],
            ],
        ],
    ]);
    $services = $container->services();

    $services->defaults()
        ->public();

    $services->set(MemoryAdapterFactory::class)
        ->tag('shopwell.filesystem.factory');

    $services->alias('messenger.test_receiver_locator', 'messenger.receiver_locator')
        ->public();

    $services->set('messenger.bus.test_shopwell', TraceableMessageBus::class)
        ->decorate('messenger.default_bus')
        ->args([service('.inner')]);

    $services->set('mailer.mailer', Mailer::class)
        ->args([
            service('mailer.transports'),
            service('messenger.default_bus'),
            service('debug.event_dispatcher')->ignoreOnInvalid(),
        ]);

    $services->alias('test.browser', 'test.client');

    $services->set('test.client', TestBrowser::class)
        ->share(false)
        ->public()
        ->args([
            service('kernel'),
            '%test.client.parameters%',
            service('test.client.history'),
            service('test.client.cookiejar'),
        ]);

    $services->set(NullHandler::class);

    $services->set(MockHandler::class)
        ->public()
        ->args([[]]);

    $services->set('test_payment_decoration', AppPaymentHandler::class)
        ->decorate(AppPaymentHandler::class)
        ->args([
            service(StateMachineRegistry::class),
            service(PaymentPayloadService::class),
            service('order_transaction_capture_refund.repository'),
            service('order_transaction.repository'),
            service('app.repository'),
            service(Connection::class),
        ]);
};
