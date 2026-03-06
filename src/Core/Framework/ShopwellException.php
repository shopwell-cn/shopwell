<?php declare(strict_types=1);

namespace Shopwell\Core\Framework;

use Shopwell\Core\Framework\Log\Package;

#[Package('framework')]
interface ShopwellException extends \Throwable
{
    public function getErrorCode(): string;

    /**
     * @return array<string|int, mixed|null>
     */
    public function getParameters(): array;
}
