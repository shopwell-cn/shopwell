<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Document\Service;

use Shopwell\Core\Checkout\Document\Renderer\RenderedDocument;
use Shopwell\Core\Framework\Log\Package;

#[Package('after-sales')]
abstract class AbstractDocumentTypeRenderer
{
    abstract public function getContentType(): string;

    abstract public function render(RenderedDocument $document): string;

    abstract public function getDecorated(): AbstractDocumentTypeRenderer;
}
