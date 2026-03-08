<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Customer\Validation\Constraint;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[Package('checkout')]
class CustomerPasswordMatches extends Constraint
{
    final public const string CUSTOMER_PASSWORD_NOT_CORRECT = 'fe2faa88-34d9-4c3b-99b3-8158b1ed8dc7';

    protected const array ERROR_NAMES = [
        self::CUSTOMER_PASSWORD_NOT_CORRECT => 'CUSTOMER_PASSWORD_NOT_CORRECT',
    ];

    public string $message;

    public SalesChannelContext $salesChannelContext;

    #[HasNamedArguments]
    public function __construct(
        SalesChannelContext $salesChannelContext,
        string $message = 'Your password is wrong'
    ) {
        parent::__construct();

        $this->salesChannelContext = $salesChannelContext;
        $this->message = $message;
    }
}
