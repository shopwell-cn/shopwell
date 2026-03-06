<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\DataAbstractionLayer\Exception;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\ShopwellException;
use Shopwell\Core\Framework\ShopwellHttpException;
use Symfony\Component\HttpFoundation\Response;

#[Package('framework')]
class SearchRequestException extends ShopwellHttpException
{
    /**
     * @param array<string, list<\Throwable>> $exceptions
     */
    public function __construct(private array $exceptions = [])
    {
        parent::__construct('Mapping failed, got {{ numberOfFailures }} failure(s).', ['numberOfFailures' => \count($exceptions)]);
    }

    public function add(\Throwable $exception, string $pointer): void
    {
        $this->exceptions[$pointer][] = $exception;
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }

    public function tryToThrow(): void
    {
        if ($this->exceptions === []) {
            return;
        }

        throw $this;
    }

    public function getErrors(bool $withTrace = false): \Generator
    {
        foreach ($this->exceptions as $pointer => $innerExceptions) {
            /** @var ShopwellException $exception */
            foreach ($innerExceptions as $exception) {
                $parameters = [];
                if ($exception instanceof ShopwellException) {
                    $parameters = $exception->getParameters();
                }

                $error = [
                    'status' => (string) $this->getStatusCode(),
                    'code' => $exception->getErrorCode(),
                    'title' => Response::$statusTexts[Response::HTTP_BAD_REQUEST],
                    'detail' => $exception->getMessage(),
                    'source' => ['pointer' => $pointer],
                    'meta' => [
                        'parameters' => $parameters,
                    ],
                ];

                if ($withTrace) {
                    $error['trace'] = $exception->getTraceAsString();
                }

                yield $error;
            }
        }
    }

    public function getErrorCode(): string
    {
        return 'FRAMEWORK__SEARCH_REQUEST_MAPPING';
    }
}
