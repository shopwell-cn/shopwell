<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Shopwell\Core\Content\Media\File\FileUrlValidatorInterface;
use Shopwell\Core\Content\Test\Media\File\FileUrlValidatorStub;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(FileUrlValidatorInterface::class, FileUrlValidatorStub::class);
};
