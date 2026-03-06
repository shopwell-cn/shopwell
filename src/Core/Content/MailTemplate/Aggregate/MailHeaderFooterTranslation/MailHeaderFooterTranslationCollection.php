<?php declare(strict_types=1);

namespace Shopwell\Core\Content\MailTemplate\Aggregate\MailHeaderFooterTranslation;

use Shopwell\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopwell\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<MailHeaderFooterTranslationEntity>
 */
#[Package('after-sales')]
class MailHeaderFooterTranslationCollection extends EntityCollection
{
    /**
     * @return array<string>
     */
    public function getLanguageIds(): array
    {
        return $this->fmap(fn (MailHeaderFooterTranslationEntity $mailTemplateTranslation) => $mailTemplateTranslation->getLanguageId());
    }

    public function filterByLanguageId(string $id): self
    {
        return $this->filter(fn (MailHeaderFooterTranslationEntity $mailTemplateTranslation) => $mailTemplateTranslation->getLanguageId() === $id);
    }

    public function getApiAlias(): string
    {
        return 'mail_template_header_footer_translation_collection';
    }

    protected function getExpectedClass(): string
    {
        return MailHeaderFooterTranslationEntity::class;
    }
}
