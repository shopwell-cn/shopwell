<?php declare(strict_types=1);

namespace Shopwell\Core\System\NumberRange\Exception;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\ShopwellHttpException;

#[Package('framework')]
class IncrementStorageNotFoundException extends ShopwellHttpException
{
    /**
     * @param array<string> $availableStorages
     */
    public function __construct(
        string $configuredStorage,
        array $availableStorages
    ) {
        parent::__construct(
            'The number range increment storage "{{ configuredStorage }}" is not available. Available storages are: "{{ availableStorages }}".',
            [
                'configuredStorage' => $configuredStorage,
                'availableStorages' => implode('", "', $availableStorages),
            ]
        );
    }

    public function getErrorCode(): string
    {
        return 'FRAMEWORK__INCREMENT_STORAGE_NOT_FOUND';
    }
}
