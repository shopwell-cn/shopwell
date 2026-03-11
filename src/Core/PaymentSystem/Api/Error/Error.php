<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Api\Error;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Struct\AssignArrayInterface;
use Shopwell\Core\Framework\Struct\AssignArrayTrait;
use Shopwell\Core\Framework\Struct\JsonSerializableTrait;

#[Package('payment-system')]
abstract class Error extends \Exception implements \JsonSerializable, AssignArrayInterface
{
    // allows to assign array data to this object
    use AssignArrayTrait;

    // allows json_encode and to decode object via json serializer
    use JsonSerializableTrait;
}
