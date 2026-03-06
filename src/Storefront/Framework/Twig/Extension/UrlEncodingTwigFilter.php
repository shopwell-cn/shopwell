<?php declare(strict_types=1);

namespace Shopwell\Storefront\Framework\Twig\Extension;

use Shopwell\Core\Content\Media\MediaEntity;
use Shopwell\Core\Framework\Feature;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Util\UrlEncoder;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

#[Package('framework')]
class UrlEncodingTwigFilter extends AbstractExtension
{
    /**
     * @return list<TwigFilter>
     */
    public function getFilters()
    {
        return [
            new TwigFilter('sw_encode_url', $this->encodeUrl(...)),
            new TwigFilter('sw_encode_media_url', $this->encodeMediaUrl(...)),
        ];
    }

    public function encodeUrl(?string $mediaUrl): ?string
    {
        return UrlEncoder::encodeUrl($mediaUrl);
    }

    public function encodeMediaUrl(?MediaEntity $media): ?string
    {
        if ($media === null || !$media->hasFile()) {
            return null;
        }

        if (!Feature::isActive('v6.8.0.0')) {
            return $this->encodeUrl($media->getUrl());
        }

        return $media->getUrl();
    }
}
