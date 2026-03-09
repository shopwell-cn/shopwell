<?php declare(strict_types=1);

namespace Shopwell\Core\System\DataDict;

use Shopwell\Core\Framework\Adapter\Cache\CacheValueCompressor;
use Shopwell\Core\Framework\Log\Package;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
#[Package('data-services')]
class CachedDataDictLoader extends AbstractDataDictLoader
{
    final public const string CACHE_KEY = 'data-dict';

    /**
     * @internal
     */
    public function __construct(
        private readonly AbstractDataDictLoader $decorated,
        private readonly CacheInterface $cache
    ) {
    }

    public function getDecorated(): AbstractDataDictLoader
    {
        return $this->decorated;
    }

    /**
     * @return array<string,mixed>
     */
    public function load(): array
    {
        $value = $this->cache->get(self::CACHE_KEY, fn (ItemInterface $item) => CacheValueCompressor::compress(
            $this->getDecorated()->load()
        ));

        return CacheValueCompressor::uncompress($value);
    }
}
