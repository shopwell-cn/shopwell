<?php declare(strict_types=1);

use Shopwell\Core\Content\Media\File\FileUrlValidatorInterface;
use Shopwell\Core\Content\Test\Media\File\FileUrlValidatorStub;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(FileUrlValidatorInterface::class, FileUrlValidatorStub::class);
};
