<?php declare(strict_types=1);

namespace Shopwell\Core\System\DataDict;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Plugin\Exception\DecorationPatternException;

#[Package('data-services')]
class DataDictLoader extends AbstractDataDictLoader
{
    public function __construct(
    ) {
    }

    public function getDecorated(): AbstractDataDictLoader
    {
        throw new DecorationPatternException(self::class);
    }

    public function load(): array
    {
        return [];
    }
}
