<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\App\Manifest\Xml\CustomField\CustomFieldTypes;

use Shopwell\Core\Framework\Log\Package;

/**
 * @internal only for use by the app-system
 */
#[Package('framework')]
class MultiSelectField extends SingleSelectField
{
    public const COMPONENT_NAME = 'sw-multi-select';
}
