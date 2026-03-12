<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Shopwell\Core\Framework\Adapter\Command\CacheWatchDelayedCommand;
use Shopwell\Core\Profiling\Integration\Datadog;
use Shopwell\Core\Profiling\Integration\ServerTiming;
use Shopwell\Core\Profiling\Integration\Stopwatch;
use Shopwell\Core\Profiling\Integration\Tideways;
use Shopwell\Core\Profiling\Profiler;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(Stopwatch::class)
        ->args([service('debug.stopwatch')->nullOnInvalid()])
        ->tag('shopwell.profiler', ['integration' => 'Symfony']);

    $services->set(Tideways::class)
        ->tag('shopwell.profiler', ['integration' => 'Tideways']);

    $services->set(CacheWatchDelayedCommand::class)
        ->args([
            service('event_dispatcher'),
            service('service_container'),
        ])
        ->tag('console.command');

    $services->set(Datadog::class)
        ->tag('shopwell.profiler', ['integration' => 'Datadog']);

    $services->set(ServerTiming::class)
        ->tag('shopwell.profiler', ['integration' => 'ServerTiming'])
        ->tag('kernel.event_listener', ['event' => 'kernel.response', 'method' => 'onResponseEvent']);

    $services->set(Profiler::class)
        ->public()
        ->args([
            tagged_iterator('shopwell.profiler', indexAttribute: 'integration'),
            '%shopwell.profiler.integrations%',
        ]);
};
