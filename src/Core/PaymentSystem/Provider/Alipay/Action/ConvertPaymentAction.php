<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Provider\Alipay\Action;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Struct\Struct;
use Shopwell\Core\PaymentSystem\Gateway\Action\ActionInterface;
use Shopwell\Core\PaymentSystem\Gateway\Request\Convert;
use Shopwell\Core\PaymentSystem\Order\Aggregate\PaymentOrderTransaction\PaymentOrderTransactionEntity;

#[Package('payment-system')]
class ConvertPaymentAction implements ActionInterface
{
    /**
     * @param Convert $request
     */
    public function execute(Struct $request): void
    {
        /** @var PaymentOrderTransactionEntity $payment */
        $payment = $request->source;

        $bizContent = [
            'out_trade_no' => $payment->getNumber(),
            'total_amount' => number_format($payment->getTotalAmount() / 100, 2),
            'subject' => $payment->getSubject(),
            'body' => $payment->getBody(),
        ];

        $details = ArrayObject::ensureArrayObject($payment->getDetails());
        $details['biz_content'] = $bizContent;
        $details['paymentType'] = $payment->getPaymentType();

        $request->result((array) $details);
    }

    public function supports(mixed $request): bool
    {
        return $request instanceof Convert
            && $request->source instanceof PaymentOrderTransactionEntity
            && $request->to === 'array';
    }
}
