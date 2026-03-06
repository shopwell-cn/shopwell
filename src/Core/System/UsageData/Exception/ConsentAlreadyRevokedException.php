<?php declare(strict_types=1);

namespace Shopwell\Core\System\UsageData\Exception;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\System\UsageData\UsageDataException;

/**
 * @internal
 */
#[Package('data-services')]
class ConsentAlreadyRevokedException extends UsageDataException
{
}
