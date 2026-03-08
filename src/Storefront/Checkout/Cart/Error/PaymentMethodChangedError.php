<?php declare(strict_types=1);

namespace Shopwell\Storefront\Checkout\Cart\Error;

use Shopwell\Core\Checkout\Cart\Error\Error;
use Shopwell\Core\Framework\Log\Package;

#[Package('checkout')]
class PaymentMethodChangedError extends Error
{
    private const string KEY = 'payment-method-changed';

    protected readonly string $oldPaymentMethodId;

    protected readonly string $oldPaymentMethodName;

    protected readonly string $newPaymentMethodId;

    protected readonly string $newPaymentMethodName;

    protected readonly string $reason;

    public function __construct(
        string $oldPaymentMethodId,
        string $oldPaymentMethodName,
        string $newPaymentMethodId,
        string $newPaymentMethodName,
        string $reason
    ) {
        $this->oldPaymentMethodId = $oldPaymentMethodId;
        $this->oldPaymentMethodName = $oldPaymentMethodName;
        $this->newPaymentMethodId = $newPaymentMethodId;
        $this->newPaymentMethodName = $newPaymentMethodName;
        $this->reason = $reason;

        $this->message = \sprintf(
            '%s payment is not available for your current cart, the payment was changed to %s. Reason: %s',
            $oldPaymentMethodName,
            $newPaymentMethodName,
            $reason
        );

        parent::__construct($this->message);
    }

    public function isPersistent(): bool
    {
        return true;
    }

    public function getParameters(): array
    {
        return [
            'oldPaymentMethodId' => $this->oldPaymentMethodId,
            'oldPaymentMethodName' => $this->oldPaymentMethodName,
            'newPaymentMethodId' => $this->newPaymentMethodId,
            'newPaymentMethodName' => $this->newPaymentMethodName,
            'reason' => $this->reason,
        ];
    }

    public function blockOrder(): bool
    {
        return false;
    }

    public function getId(): string
    {
        return \sprintf('%s-%s-%s', self::KEY, $this->oldPaymentMethodId, $this->newPaymentMethodId);
    }

    public function getLevel(): int
    {
        return self::LEVEL_NOTICE;
    }

    public function getMessageKey(): string
    {
        return self::KEY;
    }

    public function getOldPaymentMethodId(): string
    {
        return $this->oldPaymentMethodId;
    }

    public function getOldPaymentMethodName(): string
    {
        return $this->oldPaymentMethodName;
    }

    public function getNewPaymentMethodId(): string
    {
        return $this->newPaymentMethodId;
    }

    public function getNewPaymentMethodName(): string
    {
        return $this->newPaymentMethodName;
    }

    public function getReason(): string
    {
        return $this->reason;
    }
}
