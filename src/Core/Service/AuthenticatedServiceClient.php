<?php declare(strict_types=1);

namespace Shopwell\Core\Service;

use GuzzleHttp\Client;
use Shopwell\Core\Framework\App\Payload\Source;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Service\ServiceRegistry\ServiceEntry;

/**
 * @internal
 */
#[Package('framework')]
class AuthenticatedServiceClient
{
    public function __construct(
        public readonly Client $client,
        private readonly ServiceEntry $entry,
        private readonly Source $source,
    ) {
    }

    public function syncLicense(string $licenseKey = '', string $licenseHost = ''): void
    {
        if ($this->entry->licenseSyncEndPoint === null) {
            return;
        }

        $payload = [
            'source' => $this->source->jsonSerialize(),
            'licenseKey' => $licenseKey,
            'licenseHost' => $licenseHost,
        ];

        try {
            $this->client->post($this->entry->licenseSyncEndPoint, ['json' => $payload]);
        } catch (\Throwable $exception) {
            throw ServiceException::requestTransportError($exception);
        }
    }
}
