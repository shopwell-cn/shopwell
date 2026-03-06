<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Webhook\Event;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Webhook\Webhook;

/**
 * @internal
 */
#[Package('framework')]
class PreWebhooksDispatchEvent
{
    /**
     * @param list<Webhook> $webhooks
     */
    public function __construct(public array $webhooks)
    {
    }
}
