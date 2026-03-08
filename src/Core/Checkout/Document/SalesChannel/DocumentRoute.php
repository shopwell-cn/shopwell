<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Document\SalesChannel;

use Shopwell\Core\Checkout\Customer\Service\GuestAuthenticator;
use Shopwell\Core\Checkout\Document\DocumentCollection;
use Shopwell\Core\Checkout\Document\DocumentDefinition;
use Shopwell\Core\Checkout\Document\DocumentException;
use Shopwell\Core\Checkout\Document\Renderer\RenderedDocument;
use Shopwell\Core\Checkout\Document\Renderer\ZugferdRenderer;
use Shopwell\Core\Checkout\Document\Service\AbstractDocumentTypeRenderer;
use Shopwell\Core\Checkout\Document\Service\DocumentGenerator;
use Shopwell\Core\Checkout\Document\Service\PdfRenderer;
use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopwell\Core\Framework\Feature;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopwell\Core\Framework\Routing\StoreApiRouteScope;
use Shopwell\Core\PlatformRequest;
use Shopwell\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StoreApiRouteScope::ID]])]
#[Package('after-sales')]
final class DocumentRoute extends AbstractDocumentRoute
{
    public const string ACCEPT_WILDCARD = '*/*';

    /**
     * @internal
     *
     * @param EntityRepository<DocumentCollection> $documentRepository
     * @param AbstractDocumentTypeRenderer[] $renderers
     */
    public function __construct(
        private readonly DocumentGenerator $documentGenerator,
        private readonly EntityRepository $documentRepository,
        private readonly GuestAuthenticator $guestAuthenticator,
        private readonly iterable $renderers,
    ) {
    }

    public function getDecorated(): AbstractDocumentRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(
        path: '/store-api/document/download/{documentId}/{deepLinkCode}',
        name: 'store-api.document.download',
        methods: [Request::METHOD_GET, Request::METHOD_POST],
        defaults: [PlatformRequest::ATTRIBUTE_ENTITY => DocumentDefinition::ENTITY_NAME]
    )]
    public function download(
        string $documentId,
        Request $request,
        SalesChannelContext $context,
        string $deepLinkCode = '',
        ?string $fileType = null
    ): Response {
        $this->checkAuth($documentId, $request, $context);

        $isGuest = $context->getCustomer() === null || $context->getCustomer()->getGuest();
        if ($isGuest && $deepLinkCode === '') {
            throw DocumentException::customerNotLoggedIn();
        }

        $download = $request->query->getBoolean('download');

        $fileTypes = $this->resolveRequest($request, $fileType);

        $document = $this->readDocument(
            $documentId,
            $context->getContext(),
            $deepLinkCode,
            $fileTypes,
        );

        if ($document === null) {
            if (!Feature::isActive('v6.8.0.0')) {
                /*
                 * this response code needs to be removed also in the api-schema-docs:
                 * src/Core/Framework/Api/ApiDefinition/Generator/Schema/StoreApi/paths/document.json
                 */
                return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
            }

            throw DocumentException::documentFileTypeUnavailable(
                $documentId,
                $fileTypes
            );
        }

        return $this->createResponse(
            $document->getName(),
            $document->getContent(),
            $download,
            $document->getContentType()
        );
    }

    /**
     * @return list<string>
     */
    public function resolveRequest(Request $request, ?string $fileType): array
    {
        $supportedTypesMapping = $this->getSupportedFileTypes();

        /*
         * handle param fileType
         */
        if ($fileType !== null) {
            if (!Feature::isActive('v6.8.0.0')) {
                return [$fileType];
            }

            if (!isset($supportedTypesMapping[$fileType])) {
                throw DocumentException::documentFileTypeNotSupported($fileType);
            }

            return [$fileType];
        }

        /*
         * handle Accept header
         */
        $this->registerFileTypes($supportedTypesMapping, $request);

        $requestedTypesMapping = $this->getRequestedFileTypes($request);

        $supportedRequestedFormats = array_filter(
            $requestedTypesMapping,
            fn (string $fileType) => isset($supportedTypesMapping[$fileType]),
            \ARRAY_FILTER_USE_KEY
        );

        if ($supportedRequestedFormats === []) {
            throw DocumentException::documentAcceptHeaderMimeTypesNotSupported(
                array_values($requestedTypesMapping),
                array_values($supportedTypesMapping)
            );
        }

        return array_keys($supportedRequestedFormats);
    }

    private function createResponse(string $filename, string $content, bool $forceDownload, string $contentType): Response
    {
        $response = new Response($content);

        $disposition = HeaderUtils::makeDisposition(
            $forceDownload ? HeaderUtils::DISPOSITION_ATTACHMENT : HeaderUtils::DISPOSITION_INLINE,
            $filename,
            // only printable ascii
            preg_replace('/[\x00-\x1F\x7F-\xFF]/', '_', $filename) ?? ''
        );

        $response->headers->set('Content-Type', $contentType);
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    private function checkAuth(string $documentId, Request $request, SalesChannelContext $context): void
    {
        $criteria = new Criteria([$documentId])
            ->addAssociations(['order.orderCustomer.customer', 'order.billingAddress']);

        $document = $this->documentRepository->search($criteria, $context->getContext())->getEntities()->first();
        if (!$document) {
            throw DocumentException::documentNotFound($documentId);
        }

        $order = $document->getOrder();
        if (!$order) {
            throw DocumentException::orderNotFound($document->getOrderId());
        }

        $orderCustomer = $order->getOrderCustomer();
        if (!$orderCustomer) {
            throw DocumentException::customerNotLoggedIn();
        }

        if ($orderCustomer->getCustomerId() === $context->getCustomer()?->getId()) {
            return;
        }

        $this->guestAuthenticator->validate($order, $request);
    }

    /**
     * @return array<string, string> - fileType => mimeType mapping
     */
    private function getSupportedFileTypes(): array
    {
        $supportedFileTypes = [];
        $renderers = $this->renderers instanceof \Traversable ? iterator_to_array($this->renderers) : $this->renderers;

        foreach ($renderers as $fileType => $renderer) {
            $supportedFileTypes[$fileType] = $renderer->getContentType();
        }

        /*
         * Zugferd xml is not rendered by a file renderer
         * its generated in the document renderer itself
         * therefor it's not registered by document_type.renderer key="xml" and needs to be done manually
         *
         */
        $supportedFileTypes[ZugferdRenderer::FILE_EXTENSION] = ZugferdRenderer::FILE_CONTENT_TYPE;

        return $supportedFileTypes;
    }

    /**
     * @param array<string, string> $supportedFileTypes
     */
    private function registerFileTypes(array $supportedFileTypes, Request $request): void
    {
        foreach ($supportedFileTypes as $fileType => $mimeType) {
            $request->setFormat($fileType, [$mimeType]);
        }
    }

    /**
     * @return array<string, string>
     */
    private function getRequestedFileTypes(Request $request): array
    {
        $requestedFileTypes = $request->getAcceptableContentTypes();
        $fileTypes = [];

        if ($requestedFileTypes === []) {
            return $this->getDefaultFileTypes();
        }

        foreach ($requestedFileTypes as $mimeType) {
            if ($mimeType === self::ACCEPT_WILDCARD) {
                return $this->getDefaultFileTypes();
            }

            if ($fileType = $request->getFormat($mimeType)) {
                $fileTypes[$fileType] = $mimeType;
            } else {
                // keep unmapped mime type for exception output
                $fileTypes[$mimeType] = $mimeType;
            }
        }

        return $fileTypes;
    }

    /**
     * @return array<string, string>
     */
    private function getDefaultFileTypes(): array
    {
        return [PdfRenderer::FILE_EXTENSION => PdfRenderer::FILE_CONTENT_TYPE];
    }

    /**
     * @param list<string> $fileTypes - ordered list of fileTypes
     *                                storefront path param: single entry
     *                                accept header: multiple entries are possible in order of preference
     */
    private function readDocument(
        string $documentId,
        Context $context,
        string $deepLinkCode,
        array $fileTypes,
    ): ?RenderedDocument {
        $document = null;

        foreach ($fileTypes as $fileType) {
            $document = $this->documentGenerator->readDocument(
                $documentId,
                $context,
                $deepLinkCode,
                $fileType
            );

            if ($document !== null) {
                break;
            }
        }

        return $document;
    }
}
