<?php declare(strict_types=1);

namespace Shopwell\Core\Content\LandingPage;

use Shopwell\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopwell\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<LandingPageEntity>
 */
#[Package('discovery')]
class LandingPageCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return LandingPageEntity::class;
    }
}
