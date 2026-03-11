<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Gateway\Request;

use Shopwell\Core\Framework\Log\Package;

#[Package('payment-system')]
abstract class BaseGetStatus extends Generic implements GetStatusInterface
{
    protected string $status;

    public function __construct(mixed $model)
    {
        parent::__construct($model);
        $this->markUnknown();
    }

    public function getValue(): string
    {
        return $this->status;
    }
}
