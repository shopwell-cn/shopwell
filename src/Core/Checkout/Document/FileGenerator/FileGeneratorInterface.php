<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Document\FileGenerator;

use Shopwell\Core\Checkout\Document\Renderer\RenderedDocument;
use Shopwell\Core\Framework\Log\Package;

#[Package('after-sales')]
interface FileGeneratorInterface
{
    public function supports(): string;

    public function generate(RenderedDocument $html): string;

    public function getExtension(): string;

    public function getContentType(): string;
}
