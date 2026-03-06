<?php declare(strict_types=1);

namespace Shopwell\Core\Service\Requirement;

use Shopwell\Core\Framework\Log\Package;

/**
 * @internal
 *
 * A requirement that must be met before a service's permissions can be granted.
 */
#[Package('framework')]
interface ServiceRequirement
{
    public static function getName(): string;

    public function isSatisfied(): bool;
}
