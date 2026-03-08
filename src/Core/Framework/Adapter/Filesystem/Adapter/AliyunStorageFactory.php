<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Adapter\Filesystem\Adapter;

use League\Flysystem\FilesystemAdapter;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AliyunStorageFactory implements AdapterFactoryInterface
{
    public function create(array $config): FilesystemAdapter
    {
        $options = $this->resolveStorageConfig($config);

        return new AliyunStorageAdapter(
            $options['key'],
            $options['secret'],
            $options['region'],
            $options['bucket'],
        );
    }

    public function getType(): string
    {
        return 'aliyun';
    }

    /**
     * @param array<string, mixed> $definition
     *
     * @return array<string, mixed>
     */
    private function resolveStorageConfig(array $definition): array
    {
        $options = new OptionsResolver();

        $options->setRequired(['key', 'secret', 'bucket', 'region']);

        $options->setAllowedTypes('key', 'string');
        $options->setAllowedTypes('secret', 'string');
        $options->setAllowedTypes('bucket', 'string');
        $options->setAllowedTypes('region', 'string');

        /** @var array{key: string, secret: string,bucket:string,region:string} $resolved */
        $resolved = $options->resolve($definition);

        return $resolved;
    }
}
