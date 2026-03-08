<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Document;

use Shopwell\Core\Checkout\Cart\CartException;
use Shopwell\Core\Checkout\Cart\Exception\CustomerNotLoggedInException;
use Shopwell\Core\Framework\HttpException;
use Shopwell\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('after-sales')]
class DocumentException extends HttpException
{
    public const string INVALID_DOCUMENT_GENERATOR_TYPE_CODE = 'DOCUMENT__INVALID_GENERATOR_TYPE';

    public const string ORDER_NOT_FOUND = 'DOCUMENT__ORDER_NOT_FOUND';

    public const string DOCUMENT_NOT_FOUND = 'DOCUMENT__DOCUMENT_NOT_FOUND';

    public const string GENERATION_ERROR = 'DOCUMENT__GENERATION_ERROR';

    public const string DOCUMENT_NUMBER_ALREADY_EXISTS = 'DOCUMENT__NUMBER_ALREADY_EXISTS';

    public const string DOCUMENT_GENERATION_ERROR = 'DOCUMENT__GENERATION_ERROR';

    public const string DOCUMENT_INVALID_RENDERER_TYPE = 'DOCUMENT__INVALID_RENDERER_TYPE';

    public const string INVALID_REQUEST_PARAMETER_CODE = 'FRAMEWORK__INVALID_REQUEST_PARAMETER';

    public const string FILE_EXTENSION_NOT_SUPPORTED = 'DOCUMENT__FILE_EXTENSION_NOT_SUPPORTED';

    public const string CANNOT_CREATE_ZIP_FILE = 'DOCUMENT__CANNOT_CREATE_ZIP_FILE';

    public const string DOCUMENT_ZIP_READ_ERROR = 'DOCUMENT__ZIP_READ_ERROR';

    public const string DOCUMENT_FILE_TYPE_UNAVAILABLE = 'DOCUMENT__FILE_TYPE_UNAVAILABLE';

    public const string DOCUMENT_ACCEPT_HEADER_MIME_TYPES_NOT_SUPPORTED = 'DOCUMENT__ACCEPT_HEADER_MIME_TYPES_NOT_SUPPORTED';

    public const string DOCUMENT_FILE_TYPE_NOT_SUPPORTED = 'DOCUMENT__FILE_TYPE_NOT_SUPPORTED';

    public static function invalidDocumentGeneratorType(string $type): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::INVALID_DOCUMENT_GENERATOR_TYPE_CODE,
            'Unable to find a document generator with type "{{ type }}"',
            ['type' => $type]
        );
    }

    public static function orderNotFound(string $orderId, ?\Throwable $e = null): self
    {
        return new self(
            Response::HTTP_NOT_FOUND,
            self::ORDER_NOT_FOUND,
            'The order with id "{{ orderId }}" is invalid or could not be found.',
            [
                'orderId' => $orderId,
            ],
            $e
        );
    }

    public static function documentNotFound(string $documentId, ?\Throwable $e = null): self
    {
        return new self(
            Response::HTTP_NOT_FOUND,
            self::DOCUMENT_NOT_FOUND,
            'The document with id "{{ documentId }}" is invalid or could not be found.',
            [
                'documentId' => $documentId,
            ],
            $e
        );
    }

    public static function generationError(?string $message = null, ?\Throwable $e = null): self
    {
        return new self(
            Response::HTTP_NOT_FOUND,
            self::GENERATION_ERROR,
            \sprintf('Unable to generate document. %s', (string) $message),
            [
                '$message' => $message,
            ],
            $e
        );
    }

    public static function customerNotLoggedIn(): CustomerNotLoggedInException
    {
        return new CustomerNotLoggedInException(
            Response::HTTP_FORBIDDEN,
            CartException::CUSTOMER_NOT_LOGGED_IN_CODE,
            'Customer is not logged in.'
        );
    }

    public static function documentNumberAlreadyExistsException(string $number = ''): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::DOCUMENT_NUMBER_ALREADY_EXISTS,
            \sprintf('Document number %s has already been allocated.', $number),
            [
                '$number' => $number,
            ],
        );
    }

    public static function documentGenerationException(string $message = ''): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::DOCUMENT_GENERATION_ERROR,
            \sprintf('Unable to generate document. %s', $message),
            [
                '$message' => $message,
            ],
        );
    }

    public static function invalidDocumentRenderer(string $type): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::DOCUMENT_INVALID_RENDERER_TYPE,
            \sprintf('Unable to find a document renderer with type "%s"', $type),
            [
                '$type' => $type,
            ],
        );
    }

    public static function invalidRequestParameter(string $name): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::INVALID_REQUEST_PARAMETER_CODE,
            'The parameter "{{ parameter }}" is invalid.',
            ['parameter' => $name]
        );
    }

    public static function unsupportedDocumentFileExtension(string $fileExtension): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::FILE_EXTENSION_NOT_SUPPORTED,
            'File extension not supported: {{ fileExtension }}',
            ['fileExtension' => $fileExtension]
        );
    }

    /**
     * @param array<string, string[]> $violations
     */
    public static function electronicInvoiceViolation(int $count, array $violations): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::GENERATION_ERROR,
            'Unable to generate document. {{counter}} violation(s) found',
            [
                'counter' => $count,
                'violations' => $violations,
            ]
        );
    }

    public static function cannotCreateZipFile(string $filePath): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::CANNOT_CREATE_ZIP_FILE,
            'Cannot create ZIP file at "{{ filePath }}"',
            ['filePath' => $filePath]
        );
    }

    public static function cannotReadZipFile(string $filePath, ?\Throwable $previous = null): self
    {
        return new self(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::DOCUMENT_ZIP_READ_ERROR,
            'Cannot read document ZIP file: {{ filePath }}',
            ['filePath' => $filePath],
            $previous
        );
    }

    /**
     * @param array<string> $fileExtensions
     */
    public static function documentFileTypeUnavailable(string $documentId, array $fileExtensions): self
    {
        return new self(
            Response::HTTP_NOT_FOUND,
            self::DOCUMENT_FILE_TYPE_UNAVAILABLE,
            'Document with id {{ documentId }} has no generated document with file extension {{ fileExtensions }}.',
            [
                'documentId' => $documentId,
                'fileExtensions' => implode(',', $fileExtensions),
            ]
        );
    }

    /**
     * @param array<string> $requestedMimeTypes
     * @param array<string> $supportedMimeTypes
     */
    public static function documentAcceptHeaderMimeTypesNotSupported(array $requestedMimeTypes, array $supportedMimeTypes): self
    {
        return new self(
            Response::HTTP_NOT_ACCEPTABLE,
            self::DOCUMENT_ACCEPT_HEADER_MIME_TYPES_NOT_SUPPORTED,
            'The requested mime types are not supported: {{ requestedMimeTypes }}. Supported mime types are: {{ supportedMimeTypes }}.',
            [
                'requestedMimeTypes' => implode(',', $requestedMimeTypes),
                'supportedMimeTypes' => implode(',', $supportedMimeTypes),
            ]
        );
    }

    public static function documentFileTypeNotSupported(string $fileType): self
    {
        return new self(
            Response::HTTP_NOT_ACCEPTABLE,
            self::DOCUMENT_FILE_TYPE_NOT_SUPPORTED,
            'The requested file type is not supported: {{ requestedFileType }}.',
            [
                'requestedFileType' => $fileType,
            ]
        );
    }
}
