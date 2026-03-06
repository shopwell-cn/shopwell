<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\DataAbstractionLayer\Field;

use Shopwell\Core\Framework\DataAbstractionLayer\Field\Flag\Computed;
use Shopwell\Core\Framework\Log\Package;

#[Package('framework')]
class LockedField extends BoolField
{
    public function __construct()
    {
        parent::__construct('locked', 'locked');

        $this->addFlags(new Computed());
    }
}
