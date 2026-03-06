<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\App\Manifest;

use Shopwell\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('framework')]
class ManifestFactory
{
    public function createFromXmlFile(string $file): Manifest
    {
        return Manifest::createFromXmlFile($file);
    }
}
