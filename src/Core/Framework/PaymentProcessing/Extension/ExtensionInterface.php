<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\PaymentProcessing\Extension;

use Shopwell\Core\Framework\Log\Package;

#[Package('framework')]
interface ExtensionInterface
{
    public function onPreExecute(Context $context): void;

    public function onExecute(Context $context): void;

    public function onPostExecute(Context $context): void;
}
