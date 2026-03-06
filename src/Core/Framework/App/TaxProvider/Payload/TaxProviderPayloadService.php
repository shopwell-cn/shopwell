<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\App\TaxProvider\Payload;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Shopwell\Core\Checkout\Cart\TaxProvider\Struct\TaxProviderResult;
use Shopwell\Core\Framework\App\AppEntity;
use Shopwell\Core\Framework\App\Payload\AppPayloadServiceHelper;
use Shopwell\Core\Framework\App\TaxProvider\Response\TaxProviderResponse;
use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\Log\Package;

/**
 * @internal only for use by the app-system
 */
#[Package('checkout')]
class TaxProviderPayloadService
{
    public function __construct(
        private readonly AppPayloadServiceHelper $helper,
        private readonly Client $client,
    ) {
    }

    public function request(
        string $url,
        TaxProviderPayload $payload,
        AppEntity $app,
        Context $context
    ): ?TaxProviderResult {
        $optionRequest = $this->helper->createRequestOptions($payload, $app, $context);

        try {
            $response = $this->client->post($url, $optionRequest->jsonSerialize());
            $content = $response->getBody()->getContents();

            return TaxProviderResponse::create(\json_decode($content, true, 512, \JSON_THROW_ON_ERROR));
        } catch (GuzzleException) {
            return null;
        }
    }
}
