<?php declare(strict_types=1);

namespace Shopwell\Core\System\NumberRange\ValueGenerator;

use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\Log\Package;

#[Package('framework')]
interface NumberRangeValueGeneratorInterface
{
    /**
     * generates a new Value while taking Care of States, Events and Connectors
     */
    public function getValue(string $type, Context $context, ?string $salesChannelId, bool $preview = false): string;

    /**
     * generates a preview for a given pattern and start
     */
    public function previewPattern(string $definition, ?string $pattern, int $start): string;
}
