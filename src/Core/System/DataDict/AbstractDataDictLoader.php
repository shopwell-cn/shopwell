<?php declare(strict_types=1);

namespace Shopwell\Core\System\DataDict;

use Shopwell\Core\Framework\Log\Package;

#[Package('data-services')]
abstract class AbstractDataDictLoader
{
    abstract public function getDecorated(): AbstractDataDictLoader;

    /**
     * @return array<string, mixed>
     */
    abstract public function load(): array;
}
