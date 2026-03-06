<?php declare(strict_types=1);

namespace Shopwell\Core\Content\ProductExport\ScheduledTask;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Content\ProductExport\ProductExportCollection;
use Shopwell\Core\Content\ProductExport\ProductExportEntity;
use Shopwell\Core\Content\ProductExport\ProductExportException;
use Shopwell\Core\Content\ProductExport\Service\ProductExportFileHandlerInterface;
use Shopwell\Core\Content\ProductExport\Service\ProductExportGeneratorInterface;
use Shopwell\Core\Content\ProductExport\Service\ProductExportRendererInterface;
use Shopwell\Core\Content\ProductExport\Struct\ExportBehavior;
use Shopwell\Core\Content\ProductExport\Struct\ProductExportResult;
use Shopwell\Core\Defaults;
use Shopwell\Core\Framework\Adapter\Translation\AbstractTranslator;
use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Routing\Exception\SalesChannelNotFoundException;
use Shopwell\Core\Framework\Uuid\Uuid;
use Shopwell\Core\System\Locale\LanguageLocaleCodeProvider;
use Shopwell\Core\System\SalesChannel\Context\AbstractSalesChannelContextFactory;
use Shopwell\Core\System\SalesChannel\Context\SalesChannelContextPersister;
use Shopwell\Core\System\SalesChannel\Context\SalesChannelContextService;
use Shopwell\Core\System\SalesChannel\Context\SalesChannelContextServiceInterface;
use Shopwell\Core\System\SalesChannel\Context\SalesChannelContextServiceParameters;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @internal
 */
#[AsMessageHandler]
#[Package('inventory')]
final readonly class ProductExportPartialGenerationHandler
{
    /**
     * @internal
     *
     * @param EntityRepository<ProductExportCollection> $productExportRepository
     */
    public function __construct(
        private ProductExportGeneratorInterface $productExportGenerator,
        private AbstractSalesChannelContextFactory $salesChannelContextFactory,
        private EntityRepository $productExportRepository,
        private ProductExportFileHandlerInterface $productExportFileHandler,
        private MessageBusInterface $messageBus,
        private ProductExportRendererInterface $productExportRender,
        private AbstractTranslator $translator,
        private SalesChannelContextServiceInterface $salesChannelContextService,
        private SalesChannelContextPersister $contextPersister,
        private Connection $connection,
        private int $readBufferSize,
        private LanguageLocaleCodeProvider $languageLocaleProvider
    ) {
    }

    public function __invoke(ProductExportPartialGeneration $productExportPartialGeneration): void
    {
        $context = $this->getContext($productExportPartialGeneration);
        $productExport = $this->fetchProductExport($productExportPartialGeneration, $context);

        if (!$productExport) {
            return;
        }

        $exportResult = $this->runExport($productExport, $productExportPartialGeneration->getOffset(), $context);

        $filePath = $this->productExportFileHandler->getFilePath($productExport, true);

        if ($exportResult === null) {
            $this->finalizeExport($productExport, $filePath);

            return;
        }

        $this->productExportFileHandler->writeProductExportContent(
            $exportResult->getContent(),
            $filePath,
            $productExportPartialGeneration->getOffset() > 0
        );

        if ($productExportPartialGeneration->getOffset() + $this->readBufferSize < $exportResult->getTotal()) {
            $this->messageBus->dispatch(
                new ProductExportPartialGeneration(
                    $productExportPartialGeneration->getProductExportId(),
                    $productExportPartialGeneration->getSalesChannelId(),
                    $productExportPartialGeneration->getOffset() + $this->readBufferSize
                )
            );

            return;
        }

        $this->finalizeExport($productExport, $filePath);
    }

    private function getContext(ProductExportPartialGeneration $productExportPartialGeneration): Context
    {
        $context = $this->salesChannelContextFactory->create(
            Uuid::randomHex(),
            $productExportPartialGeneration->getSalesChannelId()
        );

        if ($context->getSalesChannel()->getTypeId() !== Defaults::SALES_CHANNEL_TYPE_STOREFRONT) {
            throw new SalesChannelNotFoundException();
        }

        return $context->getContext();
    }

    private function fetchProductExport(
        ProductExportPartialGeneration $productExportPartialGeneration,
        Context $context
    ): ?ProductExportEntity {
        $criteria = new Criteria([$productExportPartialGeneration->getProductExportId()]);
        $criteria
            ->addAssociation('salesChannel')
            ->addAssociation('salesChannelDomain.salesChannel')
            ->addAssociation('salesChannelDomain.language.locale')
            ->addAssociation('productStream.filters.queries')
            ->setLimit(1);

        return $this->productExportRepository->search($criteria, $context)->getEntities()->first();
    }

    private function runExport(
        ProductExportEntity $productExport,
        int $offset,
        Context $context
    ): ?ProductExportResult {
        $this->productExportRepository->update([[
            'id' => $productExport->getId(),
            'isRunning' => true,
        ]], $context);

        return $this->productExportGenerator->generate(
            $productExport,
            new ExportBehavior(
                false,
                false,
                true,
                false,
                false,
                $offset
            )
        );
    }

    private function finalizeExport(ProductExportEntity $productExport, string $filePath): void
    {
        $domain = $productExport->getSalesChannelDomain();

        if ($domain === null) {
            throw ProductExportException::salesChannelDomainNotFound($productExport->getId());
        }

        $contextToken = Uuid::randomHex();
        $this->contextPersister->save(
            $contextToken,
            [
                SalesChannelContextService::CURRENCY_ID => $productExport->getCurrencyId(),
            ],
            $productExport->getSalesChannelId()
        );

        $context = $this->salesChannelContextService->get(
            new SalesChannelContextServiceParameters(
                $productExport->getStorefrontSalesChannelId(),
                $contextToken,
                $domain->getLanguageId(),
                $domain->getCurrencyId() ?? $productExport->getCurrencyId()
            )
        );

        $this->translator->injectSettings(
            $productExport->getStorefrontSalesChannelId(),
            $domain->getLanguageId(),
            $this->languageLocaleProvider->getLocaleForLanguageId($domain->getLanguageId()),
            $context->getContext()
        );

        $headerContent = $this->productExportRender->renderHeader($productExport, $context);
        $footerContent = $this->productExportRender->renderFooter($productExport, $context);
        $finalFilePath = $this->productExportFileHandler->getFilePath($productExport);

        $this->translator->resetInjection();

        $writeProductExportSuccessful = $this->productExportFileHandler->finalizePartialProductExport(
            $filePath,
            $finalFilePath,
            $headerContent,
            $footerContent
        );

        $this->connection->delete('sales_channel_api_context', ['token' => $contextToken]);

        if (!$writeProductExportSuccessful) {
            return;
        }

        $this->productExportRepository->update(
            [
                [
                    'id' => $productExport->getId(),
                    'generatedAt' => new \DateTime(),
                    'isRunning' => false,
                ],
            ],
            $context->getContext()
        );
    }
}
