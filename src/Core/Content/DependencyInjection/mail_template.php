<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Checkout\Document\Service\DocumentGenerator;
use Shopwell\Core\Content\Mail\Message\SendMailHandler;
use Shopwell\Core\Content\Mail\Service\MailAttachmentsBuilder;
use Shopwell\Core\Content\Mail\Service\MailFactory;
use Shopwell\Core\Content\Mail\Service\MailSender;
use Shopwell\Core\Content\Mail\Service\MailService;
use Shopwell\Core\Content\Mail\Service\SendMailTemplate;
use Shopwell\Core\Content\Mail\Subscriber\FailedMessageSubscriber;
use Shopwell\Core\Content\Mail\Transport\MailerTransportLoader;
use Shopwell\Core\Content\Mail\Transport\SmtpOauthAuthenticator;
use Shopwell\Core\Content\Mail\Transport\SmtpOauthTokenProvider;
use Shopwell\Core\Content\Mail\Transport\SmtpOauthTransportFactoryDecorator;
use Shopwell\Core\Content\MailTemplate\Aggregate\MailHeaderFooter\MailHeaderFooterDefinition;
use Shopwell\Core\Content\MailTemplate\Aggregate\MailHeaderFooterTranslation\MailHeaderFooterTranslationDefinition;
use Shopwell\Core\Content\MailTemplate\Aggregate\MailTemplateMedia\MailTemplateMediaDefinition;
use Shopwell\Core\Content\MailTemplate\Aggregate\MailTemplateTranslation\MailTemplateTranslationDefinition;
use Shopwell\Core\Content\MailTemplate\Aggregate\MailTemplateType\MailTemplateTypeDefinition;
use Shopwell\Core\Content\MailTemplate\Aggregate\MailTemplateTypeTranslation\MailTemplateTypeTranslationDefinition;
use Shopwell\Core\Content\MailTemplate\Api\MailActionController;
use Shopwell\Core\Content\MailTemplate\MailTemplateDefinition;
use Shopwell\Core\Content\Media\MediaService;
use Shopwell\Core\Framework\Adapter\Translation\Translator;
use Shopwell\Core\Framework\Adapter\Twig\StringTemplateRenderer;
use Shopwell\Core\Framework\Validation\DataValidator;
use Shopwell\Core\System\Locale\LanguageLocaleCodeProvider;
use Shopwell\Core\System\SystemConfig\SystemConfigService;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set(MailTemplateDefinition::class)
        ->tag('shopwell.entity.definition', ['entity' => 'mail_template']);

    $services->set(MailTemplateTranslationDefinition::class)
        ->tag('shopwell.entity.definition', ['entity' => 'mail_template_translation']);

    $services->set(MailTemplateTypeDefinition::class)
        ->tag('shopwell.entity.definition', ['entity' => 'mail_template_type']);

    $services->set(MailTemplateTypeTranslationDefinition::class)
        ->tag('shopwell.entity.definition', ['entity' => 'mail_template_type_translation']);

    $services->set(MailTemplateMediaDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(MailHeaderFooterDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(MailHeaderFooterTranslationDefinition::class)
        ->tag('shopwell.entity.definition');

    $services->set(MailActionController::class)
        ->public()
        ->args([
            service(MailService::class),
            service(StringTemplateRenderer::class),
        ])
        ->call('setContainer', [service('service_container')]);

    $services->set(SendMailHandler::class)
        ->args([
            service('mailer.transports'),
            service('shopwell.filesystem.private'),
            service('logger'),
        ])
        ->tag('messenger.message_handler');

    $services->set(MailSender::class)
        ->public()
        ->args([
            service('mailer.mailer'),
            service('shopwell.filesystem.private'),
            service(SystemConfigService::class),
            '%shopwell.mail.max_body_length%',
            service('logger'),
            '%shopwell.messenger.message_max_kib_size%',
            abstract_arg(''),
            '%shopwell.staging.mailing.disable_delivery%',
        ]);

    $services->set(MailFactory::class)
        ->public()
        ->args([service('validator')]);

    $services->set(MailService::class)
        ->args([
            service(DataValidator::class),
            service(StringTemplateRenderer::class),
            service(MailFactory::class),
            service(MailSender::class),
            service('media.repository'),
            service('sales_channel.repository'),
            service(SystemConfigService::class),
            service('event_dispatcher'),
            service('logger'),
            service(LanguageLocaleCodeProvider::class),
        ]);

    $services->set(SendMailTemplate::class)
        ->args([
            service(MailService::class),
            service('mail_template.repository'),
            service('logger'),
            service(Translator::class),
            service(LanguageLocaleCodeProvider::class),
            service(Connection::class),
        ]);

    $services->set(MailAttachmentsBuilder::class)
        ->public()
        ->args([
            service(MediaService::class),
            service('media.repository'),
            service(DocumentGenerator::class),
            service(Connection::class),
        ]);

    $services->alias('core_mailer', 'mailer');

    $services->set(MailerTransportLoader::class)
        ->args([
            service('mailer.transport_factory'),
            service(SystemConfigService::class),
            service(MailAttachmentsBuilder::class),
            service('shopwell.filesystem.public'),
            service('document.repository'),
        ]);

    $services->set(SmtpOauthTransportFactoryDecorator::class)
        ->decorate('mailer.transport_factory.smtp')
        ->args([
            service('Shopwell\Core\Content\Mail\Transport\SmtpOauthTransportFactoryDecorator.inner'),
            service(SmtpOauthAuthenticator::class),
        ]);

    $services->set(SmtpOauthAuthenticator::class)
        ->args([service(SmtpOauthTokenProvider::class)]);

    $services->set(SmtpOauthTokenProvider::class)
        ->args([
            service('http_client'),
            service('cache.object'),
            service(SystemConfigService::class),
        ]);

    $services->set(FailedMessageSubscriber::class)
        ->args([service(Connection::class)])
        ->tag('kernel.event_subscriber');
};
