<?php declare(strict_types=1);

namespace Shopwell\Core\Content\ImportExport\Event;

use Shopwell\Core\Content\ImportExport\Message\ImportExportMessage;
use Shopwell\Core\Framework\Log\Package;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('fundamentals@after-sales')]
class ImportExportExceptionImportExportHandlerEvent extends Event
{
    public function __construct(
        private ?\Throwable $exception,
        private readonly ImportExportMessage $message
    ) {
    }

    public function getException(): ?\Throwable
    {
        return $this->exception;
    }

    public function setException(?\Throwable $exception): void
    {
        $this->exception = $exception;
    }

    public function clearException(): void
    {
        $this->exception = null;
    }

    public function hasException(): bool
    {
        return $this->exception instanceof \Throwable;
    }

    public function getMessage(): ImportExportMessage
    {
        return $this->message;
    }
}
