<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Gateway\Extension;

use Shopwell\Core\Framework\Log\Package;

#[Package('payment-system')]
interface ExtensionInterface
{
    public function onPreExecute(Context $context): void;

    public function onExecute(Context $context): void;

    public function onPostExecute(Context $context): void;
}
