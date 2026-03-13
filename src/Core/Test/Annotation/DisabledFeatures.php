<?php declare(strict_types=1);

namespace Shopwell\Core\Test\Annotation;

use Shopwell\Core\Framework\Log\Package;

/**
 * @internal
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_CLASS)]
#[Package('framework')]
final class DisabledFeatures
{
    /**
     * @param array<string> $features
     */
    public function __construct(public array $features = [])
    {
    }
}
