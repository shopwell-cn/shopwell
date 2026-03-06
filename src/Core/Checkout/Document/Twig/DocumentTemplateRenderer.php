<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Document\Twig;

use Shopwell\Core\Checkout\Document\DocumentGenerator\Counter;
use Shopwell\Core\Checkout\Document\Event\DocumentTemplateRendererParameterEvent;
use Shopwell\Core\Framework\Adapter\Translation\AbstractTranslator;
use Shopwell\Core\Framework\Adapter\Twig\TemplateFinder;
use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Uuid\Uuid;
use Shopwell\Core\System\SalesChannel\Context\AbstractSalesChannelContextFactory;
use Shopwell\Core\System\SalesChannel\Context\SalesChannelContextService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[Package('after-sales')]
class DocumentTemplateRenderer
{
    /**
     * @internal
     */
    public function __construct(
        private readonly TemplateFinder $templateFinder,
        private readonly Environment $twig,
        private readonly AbstractTranslator $translator,
        private readonly AbstractSalesChannelContextFactory $contextFactory,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function render(
        string $view,
        array $parameters = [],
        ?Context $context = null,
        ?string $salesChannelId = null,
        ?string $languageId = null,
        ?string $locale = null
    ): string {
        // If parameters for specific language setting provided, inject to translator
        if ($context !== null && $salesChannelId !== null && $languageId !== null && $locale !== null) {
            $this->translator->injectSettings(
                $salesChannelId,
                $languageId,
                $locale,
                $context
            );
            $salesChannelContext = $this->contextFactory->create(
                Uuid::randomHex(),
                $salesChannelId,
                [SalesChannelContextService::LANGUAGE_ID => $languageId]
            );

            $parameters['context'] = $salesChannelContext;
        }

        $documentTemplateRendererParameterEvent = new DocumentTemplateRendererParameterEvent($parameters);
        $this->eventDispatcher->dispatch($documentTemplateRendererParameterEvent);
        $parameters['extensions'] = $documentTemplateRendererParameterEvent->getExtensions();

        $parameters['counter'] = new Counter();

        $view = $this->resolveView($view);

        $rendered = $this->twig->render($view, $parameters);

        // If injected translator reject it
        if ($context !== null && $salesChannelId !== null && $languageId !== null && $locale !== null) {
            $this->translator->resetInjection();
        }

        return $rendered;
    }

    /**
     * @throws LoaderError
     */
    private function resolveView(string $view): string
    {
        $this->templateFinder->reset();

        return $this->templateFinder->find($view);
    }
}
