<?php declare(strict_types=1);

namespace Shopwell\Storefront\Checkout\Cart\Error;

use Shopwell\Core\Checkout\Cart\Error\Error;
use Shopwell\Core\Framework\Log\Package;

#[Package('checkout')]
class ShippingMethodChangedError extends Error
{
    private const string KEY = 'shipping-method-changed';

    protected readonly string $oldShippingMethodId;

    protected readonly string $oldShippingMethodName;

    protected readonly string $newShippingMethodId;

    protected readonly string $newShippingMethodName;

    protected readonly string $reason;

    public function __construct(
        string $oldShippingMethodId,
        string $oldShippingMethodName,
        string $newShippingMethodId,
        string $newShippingMethodName,
        string $reason
    ) {
        $this->oldShippingMethodId = $oldShippingMethodId;
        $this->oldShippingMethodName = $oldShippingMethodName;
        $this->newShippingMethodId = $newShippingMethodId;
        $this->newShippingMethodName = $newShippingMethodName;
        $this->reason = $reason;

        $this->message = \sprintf(
            '%s shipping is not available for your current cart, the shipping was changed to %s. Reason: %s',
            $oldShippingMethodName,
            $newShippingMethodName,
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
            'oldShippingMethodId' => $this->oldShippingMethodId,
            'oldShippingMethodName' => $this->oldShippingMethodName,
            'newShippingMethodId' => $this->newShippingMethodId,
            'newShippingMethodName' => $this->newShippingMethodName,
            'reason' => $this->reason,
        ];
    }

    public function blockOrder(): bool
    {
        return false;
    }

    public function getId(): string
    {
        return \sprintf('%s-%s-%s', self::KEY, $this->oldShippingMethodId, $this->newShippingMethodId);
    }

    public function getLevel(): int
    {
        return self::LEVEL_NOTICE;
    }

    public function getMessageKey(): string
    {
        return self::KEY;
    }

    public function getOldShippingMethodId(): string
    {
        return $this->oldShippingMethodId;
    }

    public function getOldShippingMethodName(): string
    {
        return $this->oldShippingMethodName;
    }

    public function getNewShippingMethodId(): string
    {
        return $this->newShippingMethodId;
    }

    public function getNewShippingMethodName(): string
    {
        return $this->newShippingMethodName;
    }

    public function getReason(): string
    {
        return $this->reason;
    }
}
