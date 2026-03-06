<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\App\Lifecycle\Registration;

use Shopwell\Core\Framework\App\AppException;
use Shopwell\Core\Framework\App\Exception\ShopIdChangeSuggestedException;
use Shopwell\Core\Framework\App\Manifest\Manifest;
use Shopwell\Core\Framework\App\ShopId\ShopIdProvider;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Store\Services\StoreClient;

/**
 * @internal only for use by the app-system
 *
 * @final
 */
#[Package('framework')]
class HandshakeFactory
{
    public function __construct(
        private readonly string $shopUrl,
        private readonly ShopIdProvider $shopIdProvider,
        private readonly StoreClient $storeClient,
        private readonly string $shopwellVersion
    ) {
    }

    public function create(Manifest $manifest): AppHandshakeInterface
    {
        $setup = $manifest->getSetup();
        $metadata = $manifest->getMetadata();
        $appName = $metadata->getName();

        if (!$setup) {
            throw AppException::registrationFailed(
                $appName,
                \sprintf('No setup for registration provided in manifest for app "%s".', $metadata->getName())
            );
        }

        $privateSecret = $setup->getSecret();

        try {
            $shopId = $this->shopIdProvider->getShopId();
        } catch (ShopIdChangeSuggestedException $e) {
            throw AppException::registrationFailed(
                $appName,
                $e->getMessage(),
            );
        }

        if ($privateSecret) {
            return new PrivateHandshake(
                $this->shopUrl,
                $privateSecret,
                $setup->getRegistrationUrl(),
                $metadata->getName(),
                $shopId,
                $this->shopwellVersion
            );
        }

        return new StoreHandshake(
            $this->shopUrl,
            $setup->getRegistrationUrl(),
            $metadata->getName(),
            $shopId,
            $this->storeClient,
            $this->shopwellVersion
        );
    }
}
