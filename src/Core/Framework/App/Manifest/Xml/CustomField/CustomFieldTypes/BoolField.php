<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\App\Manifest\Xml\CustomField\CustomFieldTypes;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\System\CustomField\CustomFieldTypes;

/**
 * @internal only for use by the app-system
 */
#[Package('framework')]
class BoolField extends CustomFieldType
{
    protected function toEntityArray(): array
    {
        return [
            'type' => CustomFieldTypes::BOOL,
            'config' => [
                'type' => 'checkbox',
                'componentName' => 'sw-field',
                'customFieldType' => 'checkbox',
            ],
        ];
    }
}
