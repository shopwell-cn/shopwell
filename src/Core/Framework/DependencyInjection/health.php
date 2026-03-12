<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Shopwell\Core\Framework\SystemCheck\Command\SystemCheckCommand;
use Shopwell\Core\Framework\SystemCheck\SystemChecker;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(SystemCheckCommand::class)
        ->args([service(SystemChecker::class)])
        ->tag('console.command');

    $services->set(SystemChecker::class)
        ->args([tagged_iterator('shopwell.system_check')]);
};
