<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Api\Struct;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Struct\Struct;

#[Package('payment-system')]
abstract class AbstractApiStruct extends Struct
{
    final public function __construct()
    {
    }
    /**
     * @param array<string, mixed> $data
     */
    public static function create(array $data): static
    {
        /** @phpstan-ignore new.staticInAbstractClassStaticMethod (the usage of "new static" is explicitly wanted) */
        $struct = new static();
        $struct->assign($data);

        return $struct;
    }
}
