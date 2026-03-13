<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\DataAbstractionLayer;

use Shopwell\Core\Defaults;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Struct\Struct;

#[Package('framework')]
class TaxFreeConfig extends Struct
{
    public function __construct(
        public bool $enabled = false,
        public string $currencyId = Defaults::CURRENCY,
        public float $amount = 0
    ) {
    }
}
