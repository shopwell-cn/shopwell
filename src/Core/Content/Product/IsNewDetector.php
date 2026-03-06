<?php declare(strict_types=1);

namespace Shopwell\Core\Content\Product;

use Shopwell\Core\Framework\DataAbstractionLayer\Entity;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopwell\Core\System\SalesChannel\SalesChannelContext;
use Shopwell\Core\System\SystemConfig\SystemConfigService;

#[Package('inventory')]
class IsNewDetector extends AbstractIsNewDetector
{
    /**
     * @internal
     */
    public function __construct(private readonly SystemConfigService $systemConfigService)
    {
    }

    public function getDecorated(): AbstractIsNewDetector
    {
        throw new DecorationPatternException(self::class);
    }

    public function isNew(Entity $product, SalesChannelContext $context): bool
    {
        $markAsNewDayRange = $this->systemConfigService->get(
            'core.listing.markAsNew',
            $context->getSalesChannelId()
        );

        $now = new \DateTime();

        /** @var \DateTimeInterface|null $releaseDate */
        $releaseDate = $product->get('releaseDate');

        return $releaseDate instanceof \DateTimeInterface
            && $releaseDate->diff($now)->days <= $markAsNewDayRange;
    }
}
