<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\PaymentProcessing\Request;

use Shopwell\Core\Framework\Log\Package;

#[Package('framework')]
interface GetStatusInterface
{
    public function getValue(): string;

    public function markNew(): void;

    public function isNew(): bool;

    public function markCaptured(): void;

    public function isCaptured(): bool;

    public function isAuthorized(): bool;

    public function markAuthorized(): void;

    public function markPayout(): void;

    public function isPayout(): bool;

    public function isRefunded(): bool;

    public function markRefunded(): void;

    public function isSuspended(): bool;

    public function markSuspended(): void;

    public function isExpired(): bool;

    public function markExpired(): void;

    public function isCanceled(): bool;

    public function markCanceled();

    public function isPending(): bool;

    public function markPending(): void;

    public function isFailed(): bool;

    public function markFailed(): void;

    public function isUnknown(): bool;

    public function markUnknown(): void;
}
