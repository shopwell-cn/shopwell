<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Gateway\Request;

use Shopwell\Core\Framework\Log\Package;

#[Package('payment-system')]
class GetHumanStatus extends BaseGetStatus
{
    public const string STATUS_CAPTURED = 'captured';

    public const string STATUS_AUTHORIZED = 'authorized';

    public const string STATUS_PAYOUT = 'payout';

    public const string STATUS_REFUNDED = 'refunded';

    public const string STATUS_UNKNOWN = 'unknown';

    public const string STATUS_FAILED = 'failed';

    public const string STATUS_SUSPENDED = 'suspended';

    public const string STATUS_EXPIRED = 'expired';

    public const string STATUS_PENDING = 'pending';

    public const string STATUS_CANCELED = 'canceled';

    public const string STATUS_NEW = 'new';

    public function markCaptured(): void
    {
        $this->status = static::STATUS_CAPTURED;
    }

    public function isCaptured(): bool
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_CAPTURED);
    }

    public function markAuthorized(): void
    {
        $this->status = static::STATUS_AUTHORIZED;
    }

    public function isAuthorized(): bool
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_AUTHORIZED);
    }

    public function markPayout(): void
    {
        $this->status = static::STATUS_PAYOUT;
    }

    public function isPayout(): bool
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_PAYOUT);
    }

    public function markRefunded(): void
    {
        $this->status = static::STATUS_REFUNDED;
    }

    public function isRefunded(): bool
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_REFUNDED);
    }

    public function markSuspended(): void
    {
        $this->status = static::STATUS_SUSPENDED;
    }

    public function isSuspended(): bool
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_SUSPENDED);
    }

    public function markExpired(): void
    {
        $this->status = static::STATUS_EXPIRED;
    }

    public function isExpired(): bool
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_EXPIRED);
    }

    public function markCanceled(): void
    {
        $this->status = static::STATUS_CANCELED;
    }

    public function isCanceled(): bool
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_CANCELED);
    }

    public function markPending(): void
    {
        $this->status = static::STATUS_PENDING;
    }

    public function isPending(): bool
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_PENDING);
    }

    public function markFailed(): void
    {
        $this->status = static::STATUS_FAILED;
    }

    public function isFailed(): bool
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_FAILED);
    }

    public function markNew(): void
    {
        $this->status = static::STATUS_NEW;
    }

    public function isNew(): bool
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_NEW);
    }

    public function markUnknown(): void
    {
        $this->status = static::STATUS_UNKNOWN;
    }

    public function isUnknown(): bool
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_UNKNOWN);
    }

    protected function isCurrentStatusEqualTo(string $expectedStatus): bool
    {
        return $this->getValue() === $expectedStatus;
    }
}
