<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Api\Response;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Struct\Struct;

#[Package('payment-system')]
abstract class AbstractPaymentResponse extends Struct
{
    final public const string SUCCESS = 'SUCCESS';

    public string $code;

    public string $message;

    public ?array $data = null;

    final protected function __construct(string $code, string $message, ?array $data = null)
    {
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
    }

    public static function success(array $data, string $message = 'ok'): static
    {
        return new static(self::SUCCESS, $message, $data);
    }

    public static function fail(string $code, string $message, ?array $data = null): static
    {
        return new static($code, $message, $data);
    }

    public function isSuccess(): bool
    {
        return $this->code === self::SUCCESS;
    }
}
