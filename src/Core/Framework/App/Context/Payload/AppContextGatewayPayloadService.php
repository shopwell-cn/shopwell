<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\App\Context\Payload;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Shopwell\Core\Framework\App\AppEntity;
use Shopwell\Core\Framework\App\AppException;
use Shopwell\Core\Framework\App\Context\Gateway\AppContextGatewayResponse;
use Shopwell\Core\Framework\App\Payload\AppPayloadServiceHelper;
use Shopwell\Core\Framework\Gateway\Context\Command\Struct\ContextGatewayPayloadStruct;
use Shopwell\Core\Framework\Log\Package;

/**
 * @internal only for use by the app-system
 */
#[Package('framework')]
class AppContextGatewayPayloadService
{
    public function __construct(
        private readonly AppPayloadServiceHelper $helper,
        private readonly Client $client,
    ) {
    }

    public function request(string $url, ContextGatewayPayloadStruct $payload, AppEntity $app): ?AppContextGatewayResponse
    {
        $optionRequest = $this->helper->createRequestOptions(
            $payload,
            $app,
            $payload->getSalesChannelContext()->getContext()
        );

        try {
            $response = $this->client->post($url, $optionRequest->jsonSerialize());
            $content = $response->getBody()->getContents();

            return new AppContextGatewayResponse(\json_decode($content, true, flags: \JSON_THROW_ON_ERROR));
        } catch (RequestException $e) {
            throw AppException::gatewayRequestFailed($app->getName(), 'context', $e);
        }
    }
}
