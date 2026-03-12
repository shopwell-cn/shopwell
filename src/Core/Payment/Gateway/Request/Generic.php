<?php declare(strict_types=1);

namespace Shopwell\Core\Payment\Gateway\Request;

use Shopwell\Core\Framework\DataAbstractionLayer\Entity;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Struct\ArrayStruct;
use Shopwell\Core\Payment\Gateway\DataAbstractionLayer\PaymentTokenEntity;
use Shopwell\Core\Payment\Gateway\Model\ModelAggregateInterface;
use Shopwell\Core\Payment\Gateway\Model\ModelAwareInterface;

#[Package('payment-system')]
abstract class Generic implements ModelAwareInterface, ModelAggregateInterface
{
    public ?PaymentTokenEntity $token = null;

    public mixed $firstModel;

    public mixed $model;

    public function __construct(
        mixed $model
    ) {
        $this->setModel($model);
        if ($model instanceof PaymentTokenEntity) {
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
        if ($model instanceof PaymentTokenEntity) {
            return;
        }
        if ($model instanceof Entity) {
            return;
        }

        $this->firstModel = $model;
    }
}
