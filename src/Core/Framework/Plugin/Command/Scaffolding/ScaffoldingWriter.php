<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Plugin\Command\Scaffolding;

use Shopwell\Core\Framework\Log\Package;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
#[Package('framework')]
class ScaffoldingWriter
{
    public function __construct(private readonly Filesystem $filesystem)
    {
    }

    public function write(StubCollection $stubCollection, PluginScaffoldConfiguration $configuration): void
    {
        /** @var Stub $stub */
        foreach ($stubCollection as $stub) {
            if ($stub->getContent() === null) {
                continue;
            }

            $this->filesystem->dumpFile($configuration->directory . '/' . $stub->getPath(), $stub->getContent());
        }
    }
}
