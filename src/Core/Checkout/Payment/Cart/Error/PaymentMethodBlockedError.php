<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Payment\Cart\Error;

use Shopwell\Core\Checkout\Cart\Error\Error;
use Shopwell\Core\Framework\Log\Package;

#[Package('checkout')]
class PaymentMethodBlockedError extends Error
{
    private const string KEY = 'payment-method-blocked';

    protected readonly string $id;

    protected readonly string $name;

    protected readonly string $reason;

    public function __construct(
        string $id,
        string $name,
        string $reason
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->reason = $reason;

        $this->message = \sprintf(
            'Payment method %s not available. Reason: %s',
            $name,
            $reason
        );

        parent::__construct($this->message);
    }

    public function getParameters(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'reason' => $this->reason,
        ];
    }

    public function getPaymentMethodId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function getId(): string
    {
        return \sprintf('%s-%s', self::KEY, $this->id);
    }

    public function getMessageKey(): string
    {
        return self::KEY;
    }

    public function getLevel(): int
    {
        return self::LEVEL_WARNING;
    }

    public function blockOrder(): bool
    {
        return true;
    }

    public function isPersistent(): bool
    {
        return false;
    }
}
