<?php declare(strict_types=1);

namespace Shopwell\Core\System\DataDict;

use Shopwell\Core\Framework\HttpException;
use Shopwell\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('data-services')]
class DataDictException extends HttpException
{
}
