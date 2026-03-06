<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\App\Manifest\Xml\CustomField\CustomFieldTypes;

use Shopwell\Core\Framework\Log\Package;

/**
 * @internal only for use by the app-system
 */
#[Package('framework')]
class MultiEntitySelectField extends SingleEntitySelectField
{
    public const COMPONENT_NAME = 'sw-entity-multi-id-select';
}
