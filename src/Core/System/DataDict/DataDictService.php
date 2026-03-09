<?php declare(strict_types=1);

namespace Shopwell\Core\System\DataDict;

use Shopwell\Core\Framework\Log\Package;

#[Package('data-services')]
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
