<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Sso;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Sso\Config\LoginConfig;
use Shopwell\Core\Framework\Sso\Config\LoginConfigService;

/**
 * @internal
 */
#[Package('framework')]
class SsoService
{
    public function __construct(
        private readonly LoginConfigService $loginConfigService,
    ) {
    }

    public function isSso(): bool
    {
        return $this->loginConfigService->getConfig() instanceof LoginConfig;
    }
}
