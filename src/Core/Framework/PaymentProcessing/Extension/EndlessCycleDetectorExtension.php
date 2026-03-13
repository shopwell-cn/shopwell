<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\PaymentProcessing\Extension;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\PaymentProcessing\PaymentProcessingException;

/**
 * @internal
 */
#[Package('framework')]
class EndlessCycleDetectorExtension
{
    protected int $limit;

    public function __construct(int $limit = 100)
    {
        $this->limit = $limit;
    }

    public function onPreExecute(Context $context): void
    {
        if (\count($context->previous) >= $this->limit) {
            throw PaymentProcessingException::possibleEndlessCycle($this->limit);
        }
    }

    public function onExecute(Context $context): void
    {
    }

    public function onPostExecute(Context $context): void
    {
    }
}
