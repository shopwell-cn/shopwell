<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Sso\SsoUser;

use Shopwell\Core\Content\Mail\Service\AbstractMailService;
use Shopwell\Core\Content\MailTemplate\Aggregate\MailTemplateType\MailTemplateTypeCollection;
use Shopwell\Core\Content\MailTemplate\Aggregate\MailTemplateType\MailTemplateTypeEntity;
use Shopwell\Core\Content\MailTemplate\MailTemplateCollection;
use Shopwell\Core\Content\MailTemplate\MailTemplateEntity;
use Shopwell\Core\Framework\Api\Context\AdminApiSource;
use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Sso\SsoException;
use Shopwell\Core\Framework\Validation\DataBag\DataBag;
use Shopwell\Core\System\Language\LanguageCollection;
use Shopwell\Core\System\SystemConfig\SystemConfigService;
use Shopwell\Core\System\User\UserCollection;
use Shopwell\Core\System\User\UserEntity;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @internal
 */
#[Package('framework')]
class SsoUserInvitationMailService
{
    private const ADMIN_ROUTE_NAME = 'administration.index';

    /**
     * @param EntityRepository<MailTemplateCollection> $mailTemplateRepository
     * @param EntityRepository<MailTemplateTypeCollection> $mailTemplateTypeRepository
     * @param EntityRepository<UserCollection> $userRepository
     * @param EntityRepository<LanguageCollection> $languageRepository
     */
    public function __construct(
        private readonly AbstractMailService $mailService,
        private readonly SystemConfigService $systemConfigService,
        private readonly EntityRepository $mailTemplateRepository,
        private readonly EntityRepository $mailTemplateTypeRepository,
        private readonly EntityRepository $userRepository,
        private readonly EntityRepository $languageRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly string $appUrl
    ) {
    }

    public function sendInvitationMailToUser(string $recipientEmail, string $localeId, Context $context): void
    {
        $apiSource = $context->getSource();
        if (!$apiSource instanceof AdminApiSource) {
            return;
        }

        $user = $this->getUserById($apiSource->getUserId(), $context);
        $shopName = $this->systemConfigService->get('core.basicInformation.shopName');
        $senderMail = $this->systemConfigService->get('core.basicInformation.email');
        $mailTemplate = $this->getMailTemplate($localeId, $context);

        $mailData = new DataBag();
        $mailData->set('templateId', $mailTemplate?->getId());
        $mailData->set('recipients', [$recipientEmail => $recipientEmail]);
        $mailData->set('senderName', $shopName);
        $mailData->set('senderEmail', $user?->getEmail() ?? $senderMail);
        $mailData->set('subject', $mailTemplate?->getTranslation('subject'));
        $mailData->set('contentPlain', $mailTemplate?->getTranslation('contentPlain'));
        $mailData->set('contentHtml', $mailTemplate?->getTranslation('contentHtml'));

        $templateVariables = new DataBag();
        $templateVariables->set('nameOfInviter', $this->createInviterName($user));
        $templateVariables->set('storeName', $shopName);
        $templateVariables->set('invitedEmailAddress', $recipientEmail);
        $templateVariables->set('signupUrl', $this->createSingUpUrl());

        $this->mailService->send($mailData->all(), $context, $templateVariables->all());
    }

    private function createSingUpUrl(): string
    {
        return $this->appUrl . $this->urlGenerator->generate(self::ADMIN_ROUTE_NAME);
    }

    private function getMailTemplate(string $localeId, Context $context): ?MailTemplateEntity
    {
        $languageId = $this->getLanguageIdForLocale($localeId, $context);
        if ($languageId) {
            $newContext = new Context(
                $context->getSource(),
                $context->getRuleIds(),
                $context->getCurrencyId(),
                [$languageId],
                $context->getVersionId(),
                $context->getCurrencyFactor(),
                $context->considerInheritance(),
                $context->getTaxState(),
                $context->getRounding()
            );
        } else {
            $newContext = $context;
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('technicalName', 'admin_sso_user_invite'));

        $result = $this->mailTemplateTypeRepository->search($criteria, $newContext)->first();
        if (!$result instanceof MailTemplateTypeEntity) {
            throw SsoException::mailTemplateNotFound();
        }

        $criteria = new Criteria();
        $criteria->addFilter(
            new MultiFilter(
                MultiFilter::CONNECTION_AND,
                [
                    new EqualsFilter('mailTemplateTypeId', $result->getId()),
                    new EqualsFilter('systemDefault', true),
                ]
            )
        );

        return $this->mailTemplateRepository->search($criteria, $newContext)->first();
    }

    private function createInviterName(?UserEntity $user): string
    {
        $firstName = $user?->getFirstName();
        $lastName = $user?->getLastName();
        $userName = $user?->getUsername();

        if ($firstName !== null && $firstName !== '' && $lastName !== null && $lastName !== '') {
            return $firstName . ' ' . $lastName;
        }

        if ($userName !== null && $userName !== '') {
            return $userName;
        }

        return 'Administrator';
    }

    private function getUserById(?string $userId, Context $context): ?UserEntity
    {
        if ($userId === null) {
            return null;
        }

        return $this->userRepository->search(new Criteria([$userId]), $context)->first();
    }

    private function getLanguageIdForLocale(string $localeId, Context $context): ?string
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('localeId', $localeId));

        return $this->languageRepository->search($criteria, $context)->first()?->getId();
    }
}
