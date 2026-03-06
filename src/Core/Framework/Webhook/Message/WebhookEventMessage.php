<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Webhook\Message;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\MessageQueue\AsyncMessageInterface;

/**
 * @internal
 */
#[Package('framework')]
class WebhookEventMessage implements AsyncMessageInterface
{
    /**
     * @internal
     *
     * @param array<string, mixed> $payload
     * @param array<string, string> $webhookHeaders
     **/
    public function __construct(
        private readonly string $webhookEventId,
        private readonly array $payload,
        private readonly ?string $appId,
        private readonly string $webhookId,
        private readonly string $shopwellVersion,
        private readonly string $url,
        private readonly ?string $secret,
        private readonly string $languageId,
        private readonly string $userLocale,
        private readonly array $webhookHeaders = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getAppId(): ?string
    {
        return $this->appId;
    }

    public function getWebhookId(): string
    {
        return $this->webhookId;
    }

    public function getShopwellVersion(): string
    {
        return $this->shopwellVersion;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getWebhookEventId(): string
    {
        return $this->webhookEventId;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function getLanguageId(): ?string
    {
        return $this->languageId;
    }

    public function getUserLocale(): ?string
    {
        return $this->userLocale;
    }

    /**
     * @return array<string, string>
     */
    public function getWebhookHeaders(): array
    {
        return $this->webhookHeaders;
    }
}
