<?php declare(strict_types=1);

namespace Shopwell\Core\Content\Seo\Event;

use Shopwell\Core\Framework\Log\Package;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('inventory')]
class SeoUrlUpdateEvent extends Event
{
    public function __construct(protected array $seoUrls)
    {
    }

    public function getSeoUrls(): array
    {
        return $this->seoUrls;
    }
}
