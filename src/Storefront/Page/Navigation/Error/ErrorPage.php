<?php declare(strict_types=1);

namespace Shopwell\Storefront\Page\Navigation\Error;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Storefront\Page\Page;

#[Package('framework')]
class ErrorPage extends Page
{
    public function isErrorPage(): bool
    {
        return true;
    }
}
