<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Document\FileGenerator;

use Shopwell\Core\Framework\Log\Package;

#[Package('after-sales')]
class FileTypes
{
    final public const PDF = 'pdf';
    final public const XML = 'xml';
}
