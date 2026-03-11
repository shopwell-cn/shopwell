<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Gateway\Action;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Struct\ArrayStruct;
use Shopwell\Core\PaymentSystem\Gateway\GatewayAwareInterface;
use Shopwell\Core\PaymentSystem\Gateway\GatewayAwareTrait;
use Shopwell\Core\PaymentSystem\Gateway\Model\PaymentInterface;
use Shopwell\Core\PaymentSystem\Gateway\Request\Capture;
use Shopwell\Core\PaymentSystem\Gateway\Request\Convert;
use Shopwell\Core\PaymentSystem\Gateway\Request\GetHumanStatus;

#[Package('payment-system')]
class CapturePaymentAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * @param Capture $request
     */
    public function execute(mixed $request): void
    {
        /** @var PaymentInterface $payment */
        $payment = $request->model;

        $this->gateway->execute($status = new GetHumanStatus($payment));

        if ($status->isNew()) {
            $this->gateway->execute($convert = new Convert($payment, 'array', $request->token));

            $payment->details = $convert->result;
        }

        $details = ArrayStruct::ensureArrayStruct($payment->details);

        $request->model = $details;
        try {
            $this->gateway->execute($request);
        } finally {
            $payment->details = $details;
        }
    }

    public function supports(mixed $request): bool
    {
        return $request instanceof Capture
            && $request->model instanceof PaymentInterface;
    }
}
