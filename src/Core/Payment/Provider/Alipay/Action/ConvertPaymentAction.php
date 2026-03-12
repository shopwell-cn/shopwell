<?php declare(strict_types=1);

namespace Shopwell\Core\Payment\Provider\Alipay\Action;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Struct\ArrayStruct;
use Shopwell\Core\Payment\Gateway\Action\ActionInterface;
use Shopwell\Core\Payment\Gateway\Model\PaymentInterface;
use Shopwell\Core\Payment\Gateway\Request\Convert;
use Shopwell\Core\Payment\Order\PaymentOrderEntity;

#[Package('payment-system')]
class ConvertPaymentAction implements ActionInterface
{
    /**
     * @param Convert $request
     */
    public function execute(mixed $request): void
    {
        /** @var PaymentOrderEntity $payment */
        $payment = $request->source->transaction;

        $bizContent = [
            'out_trade_no' => $payment->getOrderId(),
            'total_amount' => number_format($payment->getTotalAmount() / 100, 2),
            'subject' => $payment->getSubject(),
            'body' => $payment->getBody(),
        ];

        $details = ArrayStruct::ensureArrayStruct($payment->details);
        $details['biz_content'] = $bizContent;
        $details['paymentType'] = $payment->getPaymentType();

        $request->result = $details;
    }

    public function supports(mixed $request): bool
    {
        return $request instanceof Convert
            && $request->source instanceof PaymentInterface
            && $request->to === 'array';
    }
}
