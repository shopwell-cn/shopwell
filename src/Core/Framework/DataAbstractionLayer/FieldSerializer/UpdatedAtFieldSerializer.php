<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\DataAbstractionLayer\FieldSerializer;

use Shopwell\Core\Framework\DataAbstractionLayer\DataAbstractionLayerException;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\Field;
use Shopwell\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopwell\Core\Framework\DataAbstractionLayer\Write\DataStack\KeyValuePair;
use Shopwell\Core\Framework\DataAbstractionLayer\Write\EntityExistence;
use Shopwell\Core\Framework\DataAbstractionLayer\Write\WriteParameterBag;
use Shopwell\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('framework')]
class UpdatedAtFieldSerializer extends DateTimeFieldSerializer
{
    /**
     * @throws DataAbstractionLayerException
     */
    public function encode(
        Field $field,
        EntityExistence $existence,
        KeyValuePair $data,
        WriteParameterBag $parameters
    ): \Generator {
        if (!$field instanceof UpdatedAtField) {
            throw DataAbstractionLayerException::invalidSerializerField(UpdatedAtField::class, $field);
        }
        if (!$existence->exists()) {
            return;
        }

        $data->setValue(new \DateTime());

        yield from parent::encode($field, $existence, $data, $parameters);
    }
}
