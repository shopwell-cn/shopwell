<?php declare(strict_types=1);

use Composer\Composer;
use Composer\Repository\PlatformRepository;
use GuzzleHttp\Client;
use Shopwell\Core\Framework\Adapter\Asset\FallbackUrlPackage;
use Shopwell\Core\Framework\Plugin\Composer\Factory;
use Shopwell\Core\Installer\Configuration\AdminConfigurationService;
use Shopwell\Core\Installer\Configuration\EnvConfigWriter;
use Shopwell\Core\Installer\Configuration\ShopConfigurationService;
use Shopwell\Core\Installer\Controller\DatabaseConfigurationController;
use Shopwell\Core\Installer\Controller\DatabaseImportController;
use Shopwell\Core\Installer\Controller\FinishController;
use Shopwell\Core\Installer\Controller\LicenseController;
use Shopwell\Core\Installer\Controller\RequirementsController;
use Shopwell\Core\Installer\Controller\ShopConfigurationController;
use Shopwell\Core\Installer\Controller\StartController;
use Shopwell\Core\Installer\Controller\TranslationController;
use Shopwell\Core\Installer\Database\BlueGreenDeploymentService;
use Shopwell\Core\Installer\Database\DatabaseMigrator;
use Shopwell\Core\Installer\Database\MigrationCollectionFactory;
use Shopwell\Core\Installer\Finish\SystemLocker;
use Shopwell\Core\Installer\Finish\UniqueIdGenerator;
use Shopwell\Core\Installer\License\LicenseFetcher;
use Shopwell\Core\Installer\Requirements\ConfigurationRequirementsValidator;
use Shopwell\Core\Installer\Requirements\EnvironmentRequirementsValidator;
use Shopwell\Core\Installer\Requirements\FilesystemRequirementsValidator;
use Shopwell\Core\Installer\Requirements\IniConfigReader;
use Shopwell\Core\Installer\Subscriber\InstallerLocaleListener;
use Shopwell\Core\Maintenance\System\Service\DatabaseConnectionFactory;
use Shopwell\Core\Maintenance\System\Service\SetupDatabaseAdapter;
use Shopwell\Core\System\Snippet\Service\AbstractTranslationConfigLoader;
use Shopwell\Core\System\Snippet\Service\TranslationConfigLoader;
use Shopwell\Core\System\Snippet\Struct\TranslationConfig;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('shopwell.installer.supportedLanguages', ['cs' => ['id' => 'cs-CZ', 'label' => 'Čeština'], 'da-DK' => ['id' => 'da-DK', 'label' => 'Dansk'], 'de' => ['id' => 'de-DE', 'label' => 'Deutsch'], 'en-US' => ['id' => 'en-US', 'label' => 'English (US)'], 'en' => ['id' => 'en-GB', 'label' => 'English (UK)'], 'es-ES' => ['id' => 'es-ES', 'label' => 'Español'], 'fr' => ['id' => 'fr-FR', 'label' => 'Français'], 'it' => ['id' => 'it-IT', 'label' => 'Italiano'], 'nl' => ['id' => 'nl-NL', 'label' => 'Nederlands'], 'no' => ['id' => 'nn-NO', 'label' => 'Norsk'], 'pl' => ['id' => 'pl-PL', 'label' => 'Język polski'], 'pt-PT' => ['id' => 'pt-PT', 'label' => 'Português'], 'sv-SE' => ['id' => 'sv-SE', 'label' => 'Svenska']]);
    $parameters->set('shopwell.installer.supportedCurrencies', ['EUR' => 'EUR', 'USD' => 'USD', 'GBP' => 'GBP', 'PLN' => 'PLN', 'CHF' => 'CHF', 'SEK' => 'SEK', 'DKK' => 'DKK', 'NOK' => 'NOK', 'CZK' => 'CZK']);
    $parameters->set('shopwell.installer.configurationPreselection', ['cs' => ['currency' => 'CZK'], 'da-DK' => ['currency' => 'DKK'], 'de' => ['currency' => 'EUR'], 'en-US' => ['currency' => 'USD'], 'en' => ['currency' => 'GBP'], 'es-ES' => ['currency' => 'EUR'], 'fr' => ['currency' => 'EUR'], 'it' => ['currency' => 'EUR'], 'nl' => ['currency' => 'EUR'], 'no' => ['currency' => 'NOK'], 'pl' => ['currency' => 'PLN'], 'pt-PT' => ['currency' => 'EUR'], 'sv-SE' => ['currency' => 'SEK']]);
    $parameters->set('shopwell.installer.tosUrls', ['de' => 'https://api.shopwell.com/gtc/de_DE.html', 'en' => 'https://api.shopwell.com/gtc/en_GB.html']);

    $services->set('shopwell.asset.asset', FallbackUrlPackage::class)
        ->args([
            [''],
            service('shopwell.asset.version_strategy'),
        ])
        ->tag('assets.package', ['package' => 'asset']);

    $services->set('shopwell.asset.version_strategy', EmptyVersionStrategy::class);

    $services->set(InstallerLocaleListener::class)
        ->args(['%shopwell.installer.supportedLanguages%'])
        ->tag('kernel.event_subscriber');

    $services->set(PlatformRepository::class);

    $services->set(Composer::class)
        ->args(['%kernel.project_dir%'])
        ->factory([Factory::class, 'createComposer']);

    $services->set(EnvironmentRequirementsValidator::class)
        ->args([
            service(Composer::class),
            service(PlatformRepository::class),
        ])
        ->tag('shopwell.installer.requirement');

    $services->set(FilesystemRequirementsValidator::class)
        ->args(['%kernel.project_dir%'])
        ->tag('shopwell.installer.requirement');

    $services->set(ConfigurationRequirementsValidator::class)
        ->args([service(IniConfigReader::class)])
        ->tag('shopwell.installer.requirement');

    $services->set(IniConfigReader::class);

    $services->set('shopwell.installer.guzzle', Client::class);

    $services->alias(AbstractTranslationConfigLoader::class, TranslationConfigLoader::class);

    $services->set(TranslationConfigLoader::class)
        ->args([service('filesystem')]);

    $services->set(TranslationConfig::class)
        ->public()
        ->lazy()
        ->factory([service(AbstractTranslationConfigLoader::class), 'load']);

    $services->set(LicenseFetcher::class)
        ->args([
            service('shopwell.installer.guzzle'),
            '%shopwell.installer.tosUrls%',
        ]);

    $services->set(StartController::class)
        ->public()
        ->call('setContainer', [service('service_container')]);

    $services->set(RequirementsController::class)
        ->public()
        ->args([
            tagged_iterator('shopwell.installer.requirement'),
            '%kernel.project_dir%',
        ])
        ->call('setContainer', [service('service_container')]);

    $services->set(LicenseController::class)
        ->public()
        ->args([service(LicenseFetcher::class)])
        ->call('setContainer', [service('service_container')]);

    $services->set(DatabaseConfigurationController::class)
        ->public()
        ->args([
            service('translator'),
            service(BlueGreenDeploymentService::class),
            service(SetupDatabaseAdapter::class),
            service(DatabaseConnectionFactory::class),
        ])
        ->call('setContainer', [service('service_container')]);

    $services->set(DatabaseImportController::class)
        ->public()
        ->args([
            service(DatabaseConnectionFactory::class),
            service(DatabaseMigrator::class),
        ])
        ->call('setContainer', [service('service_container')]);

    $services->set(ShopConfigurationController::class)
        ->public()
        ->args([
            service(DatabaseConnectionFactory::class),
            service(EnvConfigWriter::class),
            service(ShopConfigurationService::class),
            service(AdminConfigurationService::class),
            service('translator'),
            service(TranslationConfig::class),
            '%shopwell.installer.supportedLanguages%',
            '%shopwell.installer.supportedCurrencies%',
        ])
        ->call('setContainer', [service('service_container')]);

    $services->set(FinishController::class)
        ->public()
        ->args([
            service(SystemLocker::class),
            service(Client::class),
            '%env(string:APP_URL)%',
        ])
        ->call('setContainer', [service('service_container')]);

    $services->set(BlueGreenDeploymentService::class);

    $services->set(SetupDatabaseAdapter::class);

    $services->set(DatabaseConnectionFactory::class);

    $services->set(DatabaseMigrator::class)
        ->args([
            service(SetupDatabaseAdapter::class),
            service(MigrationCollectionFactory::class),
            '%kernel.shopwell_version%',
        ]);

    $services->set(MigrationCollectionFactory::class)
        ->args(['%kernel.project_dir%']);

    $services->set(EnvConfigWriter::class)
        ->args([
            '%kernel.project_dir%',
            service(UniqueIdGenerator::class),
        ]);

    $services->set(ShopConfigurationService::class)
        ->args([service('event_dispatcher')]);

    $services->set(AdminConfigurationService::class);

    $services->set(SystemLocker::class)
        ->args(['%kernel.project_dir%']);

    $services->set(UniqueIdGenerator::class)
        ->args(['%kernel.project_dir%']);

    $services->set(TranslationController::class)
        ->public()
        ->args(['%kernel.project_dir%'])
        ->call('setContainer', [service('service_container')]);

    $services->set(Client::class);
};
