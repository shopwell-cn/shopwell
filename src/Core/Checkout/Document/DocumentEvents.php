<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Document;

use Shopwell\Core\Checkout\Document\Renderer\CreditNoteRenderer;
use Shopwell\Core\Checkout\Document\Renderer\DeliveryNoteRenderer;
use Shopwell\Core\Checkout\Document\Renderer\InvoiceRenderer;
use Shopwell\Core\Checkout\Document\Renderer\StornoRenderer;
use Shopwell\Core\Framework\Log\Package;

#[Package('after-sales')]
class DocumentEvents
{
    public const string CREDIT_NOTE_ORDER_CRITERIA_EVENT = CreditNoteRenderer::TYPE . '.document.criteria';
    public const string DELIVERY_ORDER_CRITERIA_EVENT = DeliveryNoteRenderer::TYPE . '.document.criteria';
    public const string INVOICE_ORDER_CRITERIA_EVENT = InvoiceRenderer::TYPE . '.document.criteria';
    public const string STORNO_ORDER_CRITERIA_EVENT = StornoRenderer::TYPE . '.document.criteria';
}
