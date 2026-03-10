<?php declare(strict_types=1);

namespace Shopwell\Core\PaymentSystem\Gateway\Provider\Alipay\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Shopwell\Core\Framework\Log\Package;

#[Package('payment-system')]
class ConvertPaymentAction implements ActionInterface
{
    /**
     * @param Convert $request
     */
    public function execute(mixed $request): void
    {
        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        $bizContent = [
            'out_trade_no' => $payment->getNumber(),
            'total_amount' => number_format($payment->getTotalAmount() / 100, 2),
            'subject' => $payment->getSubject(),
            'body' => $payment->getBody(),
        ];

        $details = ArrayObject::ensureArrayObject($payment->getDetails());
        $details['biz_content'] = $bizContent;
        $details['paymentType'] = $payment->getPaymentType();

        $request->setResult((array) $details);
    }

    public function supports(mixed $request): bool
    {
        return $request instanceof Convert
            && $request->getSource() instanceof PaymentInterface
            && $request->getTo() === 'array';
    }
}
