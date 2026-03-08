<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Shipping\Cart\Error;

use Shopwell\Core\Checkout\Cart\Error\Error;
use Shopwell\Core\Framework\Log\Package;

#[Package('checkout')]
class ShippingMethodBlockedError extends Error
{
    private const string KEY = 'shipping-method-blocked';

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
            'Shipping method %s not available. Reason: %s',
            $name,
            $reason
        );

        parent::__construct($this->message);
    }

    public function isPersistent(): bool
    {
        return false;
    }

    public function getParameters(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'reason' => $this->reason,
        ];
    }

    public function getShippingMethodId(): string
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

    public function blockOrder(): bool
    {
        return true;
    }

    public function getId(): string
    {
        return \sprintf('%s-%s', self::KEY, $this->id);
    }

    public function getLevel(): int
    {
        return self::LEVEL_WARNING;
    }

    public function getMessageKey(): string
    {
        return self::KEY;
    }
}
