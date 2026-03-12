<?php declare(strict_types=1);

namespace Shopwell\Core\Payment\Gateway\Action;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Struct\Struct;

#[Package('payment-system')]
interface ActionInterface
{
    public function execute(Struct $request): void;

    public function supports(Struct $request): bool;
}
