<?php declare(strict_types=1);

namespace Shopwell\Core\Content\Media\Subscriber;

use Shopwell\Core\Content\Media\Aggregate\MediaThumbnail\MediaThumbnailCollection;
use Shopwell\Core\Content\Media\MediaEntity;
use Shopwell\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEvent;
use Shopwell\Core\Framework\Log\Package;

#[Package('discovery')]
class MediaLoadedSubscriber
{
    /**
     * @param EntityLoadedEvent<MediaEntity> $event
     */
    public function unserialize(EntityLoadedEvent $event): void
    {
        foreach ($event->getEntities() as $media) {
            if ($media->getMediaTypeRaw()) {
                /** @phpstan-ignore shopwell.unserializeUsage */
                $media->setMediaType(\unserialize($media->getMediaTypeRaw()));
            }

            if ($media->getThumbnails() !== null) {
                continue;
            }

            $thumbnails = match (true) {
                /** @phpstan-ignore shopwell.unserializeUsage */
                $media->getThumbnailsRo() !== null => \unserialize($media->getThumbnailsRo()),
                default => new MediaThumbnailCollection(),
            };

            $media->setThumbnails($thumbnails);
        }
    }
}
