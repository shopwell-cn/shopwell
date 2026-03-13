<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\PaymentProcessing\Model;

use Shopwell\Core\Framework\PaymentProcessing\Security\TokenAggregateInterface;

interface PaymentInterface extends DetailsAggregateInterface, DetailsAwareInterface, TokenAggregateInterface
{
}
