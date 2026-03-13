<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\PaymentProcessing\Request;

use Shopwell\Core\Framework\DataAbstractionLayer\Entity;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\PaymentProcessing\Model\ModelAggregateInterface;
use Shopwell\Core\Framework\PaymentProcessing\Model\ModelAwareInterface;
use Shopwell\Core\Framework\PaymentProcessing\Security\TokenInterface;
use Shopwell\Core\Framework\Struct\ArrayStruct;

#[Package('framework')]
abstract class Generic implements ModelAwareInterface, ModelAggregateInterface
{
    public ?TokenInterface $token = null;

    public mixed $firstModel;

    public mixed $model;

    public function __construct(
        mixed $model
    ) {
        $this->setModel($model);
        if ($model instanceof TokenInterface) {
            $this->token = $model;
        }
    }

    public function setModel(mixed $model): void
    {
        if (\is_array($model)) {
            $model = new ArrayStruct($model);
        }

        $this->model = $model;

        $this->setFirstModel($model);
    }

    protected function setFirstModel($model): void
    {
        if ($this->firstModel) {
            return;
        }
        if ($model instanceof TokenInterface) {
            return;
        }
        if ($model instanceof Entity) {
            return;
        }

        $this->firstModel = $model;
    }
}
