<?php declare(strict_types=1);

use Doctrine\DBAL\Connection;
use Nyholm\Psr7\Factory\Psr17Factory;
use Shopwell\Core\Content\Media\Aggregate\MediaDefaultFolder\MediaDefaultFolderDefinition;
use Shopwell\Core\Content\Media\Aggregate\MediaFolder\MediaFolderDefinition;
use Shopwell\Core\Content\Media\Aggregate\MediaFolderConfiguration\MediaFolderConfigurationDefinition;
use Shopwell\Core\Content\Media\Aggregate\MediaFolderConfigurationMediaThumbnailSize\MediaFolderConfigurationMediaThumbnailSizeDefinition;
use Shopwell\Core\Content\Media\Aggregate\MediaTag\MediaTagDefinition;
use Shopwell\Core\Content\Media\Aggregate\MediaThumbnail\MediaThumbnailDefinition;
use Shopwell\Core\Content\Media\Aggregate\MediaThumbnailSize\MediaThumbnailSizeDefinition;
use Shopwell\Core\Content\Media\Aggregate\MediaTranslation\MediaTranslationDefinition;
use Shopwell\Core\Content\Media\Api\MediaFolderController;
use Shopwell\Core\Content\Media\Api\MediaUploadController;
use Shopwell\Core\Content\Media\Api\MediaUploadV2Controller;
use Shopwell\Core\Content\Media\Api\MediaVideoCoverController;
use Shopwell\Core\Content\Media\Commands\DeleteNotUsedMediaCommand;
use Shopwell\Core\Content\Media\Commands\DeleteThumbnailsCommand;
use Shopwell\Core\Content\Media\Commands\GenerateMediaTypesCommand;
use Shopwell\Core\Content\Media\Commands\GenerateThumbnailsCommand;
use Shopwell\Core\Content\Media\Core\Application\AbstractMediaPathStrategy;
use Shopwell\Core\Content\Media\Core\Application\AbstractMediaUrlGenerator;
use Shopwell\Core\Content\Media\Core\Application\MediaLocationBuilder;
use Shopwell\Core\Content\Media\DataAbstractionLayer\MediaFolderConfigurationIndexer;
use Shopwell\Core\Content\Media\DataAbstractionLayer\MediaFolderIndexer;
use Shopwell\Core\Content\Media\DataAbstractionLayer\MediaIndexer;
use Shopwell\Core\Content\Media\File\DownloadResponseGenerator;
use Shopwell\Core\Content\Media\File\FileFetcher;
use Shopwell\Core\Content\Media\File\FileLoader;
use Shopwell\Core\Content\Media\File\FileNameProvider;
use Shopwell\Core\Content\Media\File\FileSaver;
use Shopwell\Core\Content\Media\File\FileService;
use Shopwell\Core\Content\Media\File\FileUrlValidator;
use Shopwell\Core\Content\Media\File\FileUrlValidatorInterface;
use Shopwell\Core\Content\Media\File\WindowsStyleFileNameProvider;
use Shopwell\Core\Content\Media\MediaDefinition;
use Shopwell\Core\Content\Media\MediaFolderService;
use Shopwell\Core\Content\Media\MediaService;
use Shopwell\Core\Content\Media\MediaUrlPlaceholderHandler;
use Shopwell\Core\Content\Media\MediaUrlPlaceholderHandlerInterface;
use Shopwell\Core\Content\Media\Message\DeleteFileHandler;
use Shopwell\Core\Content\Media\Message\GenerateThumbnailsHandler;
use Shopwell\Core\Content\Media\Metadata\MetadataLoader;
use Shopwell\Core\Content\Media\Metadata\MetadataLoader\ImageMetadataLoader;
use Shopwell\Core\Content\Media\SalesChannel\MediaRoute;
use Shopwell\Core\Content\Media\ScheduledTask\CleanupCorruptedMediaHandler;
use Shopwell\Core\Content\Media\ScheduledTask\CleanupCorruptedMediaTask;
use Shopwell\Core\Content\Media\Service\VideoCoverService;
use Shopwell\Core\Content\Media\Subscriber\CustomFieldsUnusedMediaSubscriber;
use Shopwell\Core\Content\Media\Subscriber\MediaCreationSubscriber;
use Shopwell\Core\Content\Media\Subscriber\MediaDeletionSubscriber;
use Shopwell\Core\Content\Media\Subscriber\MediaFolderConfigLoadedSubscriber;
use Shopwell\Core\Content\Media\Subscriber\MediaLoadedSubscriber;
use Shopwell\Core\Content\Media\Subscriber\MediaVisibilityRestrictionSubscriber;
use Shopwell\Core\Content\Media\Subscriber\VideoCoverCleanupSubscriber;
use Shopwell\Core\Content\Media\Subscriber\VideoCoverLoadedSubscriber;
use Shopwell\Core\Content\Media\Thumbnail\ExternalThumbnailCollectionNormalizer;
use Shopwell\Core\Content\Media\Thumbnail\ExternalThumbnailDataNormalizer;
use Shopwell\Core\Content\Media\Thumbnail\ThumbnailService;
use Shopwell\Core\Content\Media\Thumbnail\ThumbnailSizeCalculator;
use Shopwell\Core\Content\Media\TypeDetector\AudioTypeDetector;
use Shopwell\Core\Content\Media\TypeDetector\DefaultTypeDetector;
use Shopwell\Core\Content\Media\TypeDetector\DocumentTypeDetector;
use Shopwell\Core\Content\Media\TypeDetector\ImageTypeDetector;
use Shopwell\Core\Content\Media\TypeDetector\SpatialObjectTypeDetector;
use Shopwell\Core\Content\Media\TypeDetector\TypeDetector;
use Shopwell\Core\Content\Media\TypeDetector\VideoTypeDetector;
use Shopwell\Core\Content\Media\UnusedMediaPurger;
use Shopwell\Core\Content\Media\Upload\MediaUploadService;
use Shopwell\Core\Content\Media\Upload\PresignedUploadUrlGenerator;
use Shopwell\Core\Framework\Adapter\Cache\CacheTagCollector;
use Shopwell\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory;
use Shopwell\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopwell\Core\Framework\DataAbstractionLayer\Indexing\ChildCountUpdater;
use Shopwell\Core\Framework\DataAbstractionLayer\Indexing\TreeUpdater;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('shopwell.media.metadata.types', ['\Shopwell\Core\Content\Media\Metadata\Type\ImageMetadata', '\Shopwell\Core\Content\Media\Metadata\Type\DocumentMetadata', '\Shopwell\Core\Content\Media\Metadata\Type\VideoMetadata']);

    $services->set(MediaDefinition::class)
        ->tag('shopwell.entity.definition')
        ->tag('shopwell.entity.hookable');

    $services->set(MediaDefaultFolderDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(MediaThumbnailDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(MediaTranslationDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(MediaFolderDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(MediaThumbnailSizeDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(MediaFolderConfigurationDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(MediaFolderConfigurationMediaThumbnailSizeDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(MediaTagDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(GenerateThumbnailsHandler::class)
        ->args([
            service(ThumbnailService::class),
            service('media.repository'),
            '%shopwell.media.remote_thumbnails.enable%',
        ])
        ->tag('messenger.message_handler');

    $services->set(DeleteFileHandler::class)
        ->args([
            service('shopwell.filesystem.public'),
            service('shopwell.filesystem.private'),
        ])
        ->tag('messenger.message_handler');

    $services->set(CleanupCorruptedMediaHandler::class)
        ->args([
            service('scheduled_task.repository'),
            service('logger'),
            service('media.repository'),
        ])
        ->tag('messenger.message_handler');

    $services->set(FileService::class);

    $services->set(FileFetcher::class)
        ->args([
            service(FileUrlValidatorInterface::class),
            service(FileService::class),
            '%shopwell.media.enable_url_upload_feature%',
            '%shopwell.media.enable_url_validation%',
            '%shopwell.media.url_upload_max_size%',
        ]);

    $services->set(FileUrlValidatorInterface::class, FileUrlValidator::class);

    $services->set(FileSaver::class)
        ->public()
        ->args([
            service('media.repository'),
            service('shopwell.filesystem.public'),
            service('shopwell.filesystem.private'),
            service(ThumbnailService::class),
            service(MetadataLoader::class),
            service(TypeDetector::class),
            service('messenger.default_bus'),
            service('event_dispatcher'),
            service(MediaLocationBuilder::class),
            service(AbstractMediaPathStrategy::class),
            '%shopwell.filesystem.allowed_extensions%',
            '%shopwell.filesystem.private_allowed_extensions%',
            '%shopwell.media.remote_thumbnails.enable%',
        ]);

    $services->set(FileLoader::class)
        ->args([
            service('shopwell.filesystem.public'),
            service('shopwell.filesystem.private'),
            service('media.repository'),
            service(Psr17Factory::class),
        ]);

    $services->set(FileNameProvider::class, WindowsStyleFileNameProvider::class)
        ->args([service('media.repository')]);

    $services->set(DownloadResponseGenerator::class)
        ->args([
            service('logger'),
            service('shopwell.filesystem.public'),
            service('shopwell.filesystem.private'),
            service(MediaService::class),
            '%shopwell.filesystem.private_local_download_strategy%',
            service(AbstractMediaUrlGenerator::class),
            '%shopwell.filesystem.private_local_path_prefix%',
        ]);

    $services->set(GenerateThumbnailsCommand::class)
        ->args([
            service(ThumbnailService::class),
            service('media.repository'),
            service('media_folder.repository'),
            service('messenger.default_bus'),
            '%shopwell.media.remote_thumbnails.enable%',
        ])
        ->tag('console.command');

    $services->set(GenerateMediaTypesCommand::class)
        ->args([
            service(TypeDetector::class),
            service('media.repository'),
        ])
        ->tag('console.command');

    $services->set(DeleteNotUsedMediaCommand::class)
        ->share(false)
        ->args([
            service(UnusedMediaPurger::class),
            service('event_dispatcher'),
        ])
        ->tag('console.command');

    $services->set(DeleteThumbnailsCommand::class)
        ->args([
            service(Connection::class),
            service('media_thumbnail.repository'),
            '%shopwell.media.remote_thumbnails.enable%',
        ])
        ->tag('console.command');

    $services->set(MediaUploadController::class)
        ->public()
        ->args([
            service(MediaService::class),
            service(FileSaver::class),
            service(FileNameProvider::class),
            service(MediaDefinition::class),
            service('event_dispatcher'),
        ])
        ->call('setContainer', [service('service_container')]);

    $services->set(MediaFolderController::class)
        ->public()
        ->args([service(MediaFolderService::class)])
        ->call('setContainer', [service('service_container')]);

    $services->set(MediaUploadV2Controller::class)
        ->public()
        ->args([
            service(MediaUploadService::class),
            service('media.repository'),
        ]);

    $services->set(MediaVideoCoverController::class)
        ->public()
        ->args([service(VideoCoverService::class)])
        ->tag('controller.service_arguments')
        ->call('setContainer', [service('service_container')]);

    $services->set(ExternalThumbnailCollectionNormalizer::class)
        ->tag('serializer.normalizer');

    $services->set(ExternalThumbnailDataNormalizer::class)
        ->tag('serializer.normalizer');

    $services->set(ImageMetadataLoader::class)
        ->tag('shopwell.metadata.loader');

    $services->set(MetadataLoader::class)
        ->args([tagged_iterator('shopwell.metadata.loader')]);

    $services->set(AudioTypeDetector::class)
        ->tag('shopwell.media_type.detector', ['priority' => 10]);

    $services->set(DefaultTypeDetector::class)
        ->tag('shopwell.media_type.detector', ['priority' => 0]);

    $services->set(DocumentTypeDetector::class)
        ->tag('shopwell.media_type.detector', ['priority' => 10]);

    $services->set(ImageTypeDetector::class)
        ->tag('shopwell.media_type.detector', ['priority' => 10]);

    $services->set(VideoTypeDetector::class)
        ->tag('shopwell.media_type.detector', ['priority' => 10]);

    $services->set(SpatialObjectTypeDetector::class)
        ->tag('shopwell.media_type.detector', ['priority' => 10]);

    $services->set(TypeDetector::class)
        ->args([tagged_iterator('shopwell.media_type.detector')]);

    $services->set(UnusedMediaPurger::class)
        ->args([
            service('media.repository'),
            service(Connection::class),
            service('event_dispatcher'),
        ]);

    $services->set(MediaFolderService::class)
        ->args([
            service('media.repository'),
            service('media_folder.repository'),
            service('media_folder_configuration.repository'),
        ]);

    $services->set(ThumbnailService::class)
        ->args([
            service('media_thumbnail.repository'),
            service('shopwell.filesystem.public'),
            service('shopwell.filesystem.private'),
            service('media_folder.repository'),
            service('event_dispatcher'),
            service(MediaIndexer::class),
            service(ThumbnailSizeCalculator::class),
            service(Connection::class),
            '%shopwell.media.remote_thumbnails.enable%',
        ]);

    $services->set(MediaService::class)
        ->args([
            service('media.repository'),
            service('media_folder.repository'),
            service(FileLoader::class),
            service(FileSaver::class),
            service(FileFetcher::class),
        ]);

    $services->set(MediaUploadService::class)
        ->args([
            service('media.repository'),
            service(FileFetcher::class),
            service(FileSaver::class),
            service('event_dispatcher'),
            service('shopwell.media.upload.http_client'),
            service('media_thumbnail.repository'),
            service('media_thumbnail_size.repository'),
        ]);

    $services->set(VideoCoverService::class)
        ->args([service('media.repository')]);

    $services->set(ThumbnailSizeCalculator::class);

    $services->alias('shopwell.media.upload.http_client', 'http_client')
        ->private();

    $services->set(PresignedUploadUrlGenerator::class)
        ->args([
            service(AbstractMediaPathStrategy::class),
            '%shopwell.filesystem.public%',
            service('logger'),
        ])
        ->factory([PresignedUploadUrlGenerator::class, 'create']);

    $services->set(MediaUrlPlaceholderHandlerInterface::class, MediaUrlPlaceholderHandler::class)
        ->public()
        ->args([
            service(Connection::class),
            service(AbstractMediaUrlGenerator::class),
        ]);

    $services->set(MediaIndexer::class)
        ->args([
            service(IteratorFactory::class),
            service('media.repository'),
            service('media_thumbnail.repository'),
            service(Connection::class),
            service('event_dispatcher'),
            '%shopwell.media.remote_thumbnails.enable%',
        ])
        ->tag('shopwell.entity_indexer');

    $services->set(MediaFolderConfigurationIndexer::class)
        ->args([
            service(IteratorFactory::class),
            service('media_folder_configuration.repository'),
            service(Connection::class),
            service('event_dispatcher'),
        ])
        ->tag('shopwell.entity_indexer');

    $services->set(MediaFolderIndexer::class)
        ->args([
            service(IteratorFactory::class),
            service('media_folder.repository'),
            service(Connection::class),
            service('event_dispatcher'),
            service(ChildCountUpdater::class),
            service(TreeUpdater::class),
        ])
        ->tag('shopwell.entity_indexer');

    $services->set(MediaFolderConfigLoadedSubscriber::class)
        ->tag('kernel.event_subscriber');

    $services->set(MediaDeletionSubscriber::class)
        ->args([
            service('event_dispatcher'),
            service('media_thumbnail.repository'),
            service('messenger.default_bus'),
            service(DeleteFileHandler::class),
            service(Connection::class),
            service('media.repository'),
        ])
        ->tag('kernel.event_subscriber');

    $services->set(MediaVisibilityRestrictionSubscriber::class)
        ->tag('kernel.event_subscriber');

    $services->set(MediaCreationSubscriber::class)
        ->tag('kernel.event_subscriber');

    $services->set(CustomFieldsUnusedMediaSubscriber::class)
        ->args([
            service(Connection::class),
            service(DefinitionInstanceRegistry::class),
        ])
        ->tag('kernel.event_subscriber');

    $services->set(VideoCoverLoadedSubscriber::class)
        ->args([service('media.repository')])
        ->tag('kernel.event_subscriber');

    $services->set(VideoCoverCleanupSubscriber::class)
        ->args([service('media.repository')])
        ->tag('kernel.event_subscriber');

    $services->set(MediaLoadedSubscriber::class)
        ->tag('kernel.event_listener', ['event' => 'media.loaded', 'method' => 'unserialize', 'priority' => 100]);

    $services->set(MediaRoute::class)
        ->public()
        ->args([
            service('media.repository'),
            service(CacheTagCollector::class),
        ]);

    $services->set(CleanupCorruptedMediaTask::class)
        ->tag('shopwell.scheduled.task');
};
