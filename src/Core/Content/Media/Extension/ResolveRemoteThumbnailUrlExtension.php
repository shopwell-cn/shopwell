<?php

declare(strict_types=1);

namespace Shopwell\Core\Content\Media\Extension;

use Shopwell\Core\Content\Media\MediaEntity;
use Shopwell\Core\Framework\DataAbstractionLayer\PartialEntity;
use Shopwell\Core\Framework\Extensions\Extension;
use Shopwell\Core\Framework\Log\Package;

/**
 * @extends Extension<string|null>
 *
 * @codeCoverageIgnore
 */
#[Package('discovery')]
final class ResolveRemoteThumbnailUrlExtension extends Extension
{
    public const string NAME = 'remote_thumbnail_url.resolve';

    /**
     * @internal shopwell owns the __constructor, but the properties are public API
     */
    public function __construct(
        public string $mediaUrl,
        public string $width,
        public string $height,
        public string $pattern,
        public MediaEntity|PartialEntity $mediaEntity,
    ) {
    }
}
