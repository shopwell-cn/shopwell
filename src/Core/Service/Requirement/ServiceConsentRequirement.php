<?php declare(strict_types=1);

namespace Shopwell\Core\Service\Requirement;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Service\Permission\PermissionsService;

/**
 * @internal
 */
#[Package('framework')]
class ServiceConsentRequirement implements ServiceRequirement
{
    public const NAME = 'service_consent';

    public function __construct(
        private readonly PermissionsService $permissionsService,
    ) {
    }

    public static function getName(): string
    {
        return self::NAME;
    }

    public function isSatisfied(): bool
    {
        return $this->permissionsService->areGranted();
    }
}
