<?php declare(strict_types=1);

namespace Shopwell\Core\Content\Sitemap\Service;

use Shopwell\Core\Framework\Log\Package;

#[Package('discovery')]
interface SitemapHandleInterface
{
    public function write(array $urls): void;

    public function finish(): void;
}
