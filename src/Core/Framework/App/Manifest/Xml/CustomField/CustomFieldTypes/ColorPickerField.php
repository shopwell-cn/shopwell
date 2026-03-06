<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\App\Manifest\Xml\CustomField\CustomFieldTypes;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\System\CustomField\CustomFieldTypes;

/**
 * @internal only for use by the app-system
 */
#[Package('framework')]
class ColorPickerField extends CustomFieldType
{
    protected function toEntityArray(): array
    {
        return [
            'type' => CustomFieldTypes::TEXT,
            'config' => [
                'type' => 'colorpicker',
                'componentName' => 'sw-field',
                'customFieldType' => 'colorpicker',
            ],
        ];
    }
}
