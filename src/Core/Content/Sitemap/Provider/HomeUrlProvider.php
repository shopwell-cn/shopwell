<?php declare(strict_types=1);

namespace Shopwell\Core\Content\Sitemap\Provider;

use Shopwell\Core\Content\Sitemap\Struct\Url;
use Shopwell\Core\Content\Sitemap\Struct\UrlResult;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopwell\Core\System\SalesChannel\SalesChannelContext;

#[Package('discovery')]
class HomeUrlProvider extends AbstractUrlProvider
{
    final public const CHANGE_FREQ = 'daily';
    final public const PRIORITY = 1.0;

    public function getDecorated(): AbstractUrlProvider
    {
        throw new DecorationPatternException(self::class);
    }

    public function getName(): string
    {
        return 'home';
    }

    /**
     * {@inheritdoc}
     */
    public function getUrls(SalesChannelContext $context, int $limit, ?int $offset = null): UrlResult
    {
        $homepageUrl = new Url();
        $homepageUrl->setLoc('');
        $homepageUrl->setLastmod(new \DateTime());
        $homepageUrl->setChangefreq(self::CHANGE_FREQ);
        $homepageUrl->setPriority(self::PRIORITY);
        $homepageUrl->setResource($this->getName());
        $homepageUrl->setIdentifier('');

        return new UrlResult([$homepageUrl], null);
    }
}
