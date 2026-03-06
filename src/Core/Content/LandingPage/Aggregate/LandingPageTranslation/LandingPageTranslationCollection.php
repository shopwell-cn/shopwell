<?php declare(strict_types=1);

namespace Shopwell\Core\Content\LandingPage\Aggregate\LandingPageTranslation;

use Shopwell\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopwell\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<LandingPageTranslationEntity>
 */
#[Package('discovery')]
class LandingPageTranslationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return LandingPageTranslationEntity::class;
    }
}
