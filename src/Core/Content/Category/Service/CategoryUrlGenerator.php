<?php declare(strict_types=1);

namespace Shopwell\Core\Content\Category\Service;

use Shopwell\Core\Content\Category\CategoryDefinition;
use Shopwell\Core\Content\Category\CategoryEntity;
use Shopwell\Core\Content\Seo\SeoUrlPlaceholderHandlerInterface;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopwell\Core\System\SalesChannel\SalesChannelEntity;

#[Package('discovery')]
class CategoryUrlGenerator extends AbstractCategoryUrlGenerator
{
    /**
     * @internal
     */
    public function __construct(private readonly SeoUrlPlaceholderHandlerInterface $seoUrlReplacer)
    {
    }

    public function getDecorated(): AbstractCategoryUrlGenerator
    {
        throw new DecorationPatternException(self::class);
    }

    public function generate(CategoryEntity $category, ?SalesChannelEntity $salesChannel): ?string
    {
        if ($category->getType() === CategoryDefinition::TYPE_FOLDER) {
            return null;
        }

        if ($category->getType() !== CategoryDefinition::TYPE_LINK) {
            /** @phpstan-ignore shopwell.storefrontRouteUsage (Do not use Storefront routes in the core. Will be fixed with https://github.com/shopwell/shopwell/issues/12970) */
            return $this->seoUrlReplacer->generate('frontend.navigation.page', ['navigationId' => $category->getId()]);
        }

        $linkType = $category->getTranslation('linkType');
        $internalLink = $category->getTranslation('internalLink');

        if (!$internalLink && $linkType && $linkType !== CategoryDefinition::LINK_TYPE_EXTERNAL) {
            return null;
        }

        switch ($linkType) {
            case CategoryDefinition::LINK_TYPE_PRODUCT:
                /** @phpstan-ignore shopwell.storefrontRouteUsage (Do not use Storefront routes in the core. Will be fixed with https://github.com/shopwell/shopwell/issues/12970) */
                return $this->seoUrlReplacer->generate('frontend.detail.page', ['productId' => $internalLink]);

            case CategoryDefinition::LINK_TYPE_CATEGORY:
                if ($salesChannel !== null && $internalLink === $salesChannel->getNavigationCategoryId()) {
                    /** @phpstan-ignore shopwell.storefrontRouteUsage (Do not use Storefront routes in the core. Will be fixed with https://github.com/shopwell/shopwell/issues/12970) */
                    return $this->seoUrlReplacer->generate('frontend.home.page');
                }

                /** @phpstan-ignore shopwell.storefrontRouteUsage (Do not use Storefront routes in the core. Will be fixed with https://github.com/shopwell/shopwell/issues/12970) */
                return $this->seoUrlReplacer->generate('frontend.navigation.page', ['navigationId' => $internalLink]);

            case CategoryDefinition::LINK_TYPE_LANDING_PAGE:
                /** @phpstan-ignore shopwell.storefrontRouteUsage (Do not use Storefront routes in the core. Will be fixed with https://github.com/shopwell/shopwell/issues/12970) */
                return $this->seoUrlReplacer->generate('frontend.landing.page', ['landingPageId' => $internalLink]);

            case CategoryDefinition::LINK_TYPE_EXTERNAL:
            default:
                return $category->getTranslation('externalLink');
        }
    }
}
