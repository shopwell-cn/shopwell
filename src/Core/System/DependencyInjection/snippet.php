<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\DBAL\Connection;
use GuzzleHttp\Client;
use Shopwell\Core\System\Snippet\Aggregate\SnippetSet\SnippetSetDefinition;
use Shopwell\Core\System\Snippet\Command\InstallTranslationCommand;
use Shopwell\Core\System\Snippet\Command\LintTranslationFilesCommand;
use Shopwell\Core\System\Snippet\Command\UpdateTranslationCommand;
use Shopwell\Core\System\Snippet\Command\Util\CountryAgnosticFileLinter;
use Shopwell\Core\System\Snippet\Command\ValidateSnippetsCommand;
use Shopwell\Core\System\Snippet\Files\SnippetFileCollection;
use Shopwell\Core\System\Snippet\Service\TranslationConfigLoader;
use Shopwell\Core\System\Snippet\Service\TranslationLoader;
use Shopwell\Core\System\Snippet\Service\TranslationMetadataLoader;
use Shopwell\Core\System\Snippet\SnippetDefinition;
use Shopwell\Core\System\Snippet\SnippetFileHandler;
use Shopwell\Core\System\Snippet\SnippetFixer;
use Shopwell\Core\System\Snippet\SnippetValidator;
use Shopwell\Core\System\Snippet\SnippetValidatorInterface;
use Shopwell\Core\System\Snippet\Struct\TranslationConfig;
use Shopwell\Core\System\Snippet\Subscriber\CustomFieldSubscriber;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(SnippetSetDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(SnippetDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(SnippetValidatorInterface::class, SnippetValidator::class)
        ->args([
            service(SnippetFileCollection::class),
            service(SnippetFileHandler::class),
            '%kernel.project_dir%/',
        ]);

    $services->set(SnippetValidator::class)
        ->args([
            service(SnippetFileCollection::class),
            service(SnippetFileHandler::class),
            '%kernel.project_dir%/',
        ]);

    $services->set(SnippetFixer::class)
        ->args([service(SnippetFileHandler::class)]);

    $services->set(ValidateSnippetsCommand::class)
        ->args([
            service(SnippetValidator::class),
            service(SnippetFixer::class),
        ])
        ->tag('console.command');

    $services->set(CountryAgnosticFileLinter::class)
        ->args([
            service(Filesystem::class),
            service('plugin.repository'),
            service('app.repository'),
            inline_service(Finder::class),
        ]);

    $services->set(LintTranslationFilesCommand::class)
        ->args([service(CountryAgnosticFileLinter::class)])
        ->tag('console.command');

    $services->set(InstallTranslationCommand::class)
        ->args([
            service(TranslationLoader::class),
            service(TranslationConfig::class),
            service(TranslationMetadataLoader::class),
        ])
        ->tag('console.command');

    $services->set(UpdateTranslationCommand::class)
        ->args([
            service(TranslationLoader::class),
            service(TranslationMetadataLoader::class),
        ])
        ->tag('console.command');

    $services->set('shopwell.translation.client', Client::class);

    $services->set(TranslationConfigLoader::class)
        ->args([service('filesystem')]);

    $services->set(TranslationConfig::class)
        ->public()
        ->lazy()
        ->factory([service(TranslationConfigLoader::class), 'load']);

    $services->set(TranslationLoader::class)
        ->args([
            service('shopwell.filesystem.private'),
            service('language.repository'),
            service('locale.repository'),
            service('snippet_set.repository'),
            service('shopwell.translation.client'),
            service(TranslationConfig::class),
            service('validator'),
        ]);

    $services->set(TranslationMetadataLoader::class)
        ->args([
            service(TranslationConfig::class),
            service('shopwell.translation.client'),
            service('shopwell.filesystem.private'),
        ]);

    $services->set(SnippetFileHandler::class)
        ->args([service('filesystem')]);

    $services->set(CustomFieldSubscriber::class)
        ->args([service(Connection::class)])
        ->tag('kernel.event_subscriber');
};
