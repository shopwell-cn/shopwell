<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Event\EventData;

use Shopwell\Core\Framework\Log\Package;

#[Package('framework')]
class MailRecipientStruct
{
    public ?string $bcc = null;

    public ?string $cc = null;

    /**
     * @param array<string, mixed> $recipients ['email' => 'firstName lastName']
     */
    public function __construct(private array $recipients)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function getRecipients(): array
    {
        return $this->recipients;
    }

    /**
     * @param array<string, mixed> $recipients
     */
    public function setRecipients(array $recipients): void
    {
        $this->recipients = $recipients;
    }
}
