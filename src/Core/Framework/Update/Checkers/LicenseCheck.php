<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Update\Checkers;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Store\Services\StoreClient;
use Shopwell\Core\Framework\Update\Struct\ValidationResult;
use Shopwell\Core\System\SystemConfig\SystemConfigService;

#[Package('framework')]
class LicenseCheck
{
    /**
     * @internal
     */
    public function __construct(
        private readonly SystemConfigService $systemConfigService,
        private readonly StoreClient $storeClient
    ) {
    }

    public function check(): ValidationResult
    {
        $licenseHost = $this->systemConfigService->get('core.store.licenseHost');

        if (empty($licenseHost) || $this->storeClient->isShopUpgradeable()) {
            return new ValidationResult('validShopwellLicense', true, 'validShopwellLicense');
        }

        return new ValidationResult('invalidShopwellLicense', false, 'invalidShopwellLicense');
    }
}
