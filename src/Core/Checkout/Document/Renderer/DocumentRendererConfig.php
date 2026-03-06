<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Document\Renderer;

use Shopwell\Core\Framework\Log\Package;

#[Package('after-sales')]
final class DocumentRendererConfig
{
    public string $deepLinkCode = '';
}
