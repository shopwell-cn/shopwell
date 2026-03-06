<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Document\Renderer;

use Shopwell\Core\Checkout\Document\DocumentException;
use Shopwell\Core\Checkout\Document\Struct\DocumentGenerateOperation;
use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\Log\Package;

#[Package('after-sales')]
final class DocumentRendererRegistry
{
    /**
     * @internal
     *
     * @param AbstractDocumentRenderer[] $documentRenderers
     */
    public function __construct(protected iterable $documentRenderers)
    {
    }

    /**
     * @param array<string, DocumentGenerateOperation> $operations
     */
    public function render(string $documentType, array $operations, Context $context, DocumentRendererConfig $rendererConfig): RendererResult
    {
        foreach ($this->documentRenderers as $documentRenderer) {
            if ($documentRenderer->supports() !== $documentType) {
                continue;
            }

            return $documentRenderer->render($operations, $context, $rendererConfig);
        }

        throw DocumentException::invalidDocumentGeneratorType($documentType);
    }
}
