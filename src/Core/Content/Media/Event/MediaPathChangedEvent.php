<?php

declare(strict_types=1);

namespace Shopwell\Core\Content\Media\Event;

use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\Log\Package;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('discovery')]
class MediaPathChangedEvent extends Event
{
    /**
     * @var array<array{mediaId: string, thumbnailId: ?string, path: string, mimeType: ?string}>
     */
    public array $changed = [];

    public function __construct(public Context $context)
    {
    }

    public function mediaWithMimeType(string $mediaId, string $path, ?string $mimeType = null): void
    {
        $this->changed[] = [
            'mediaId' => $mediaId,
            'thumbnailId' => null,
            'path' => $path,
            'mimeType' => $mimeType,
        ];
    }

    public function thumbnailWithMimeType(
        string $mediaId,
        string $thumbnailId,
        string $path,
        ?string $mimeType = null
    ): void {
        $this->changed[] = [
            'mediaId' => $mediaId,
            'thumbnailId' => $thumbnailId,
            'path' => $path,
            'mimeType' => $mimeType,
        ];
    }
}
