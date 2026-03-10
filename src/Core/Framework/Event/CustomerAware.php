<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Event;

use Shopwell\Core\Framework\Log\Package;

#[Package('framework')]
#[IsFlowEventAware]
interface CustomerAware
{
    public const string CUSTOMER_ID = 'customerId';

    public const string CUSTOMER = 'customer';

    public function getCustomerId(): string;
}
