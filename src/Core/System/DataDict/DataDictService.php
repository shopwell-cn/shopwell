<?php declare(strict_types=1);

namespace Shopwell\Core\System\DataDict;

class DataDictService
{
    /**
     * @internal
     */
    public function __construct(
        private readonly AbstractDataDictLoader $loader
    ) {
    }
}
