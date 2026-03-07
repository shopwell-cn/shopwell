<?php declare(strict_types=1);

namespace Shopwell\Core;

require __DIR__ . '/TestBootstrapper.php';

new TestBootstrapper()
    ->setPlatformEmbedded(false)
    ->setEnableCommercial()
    ->bootstrap();
