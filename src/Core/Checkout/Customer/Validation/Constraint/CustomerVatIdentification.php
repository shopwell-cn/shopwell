<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Customer\Validation\Constraint;

use Shopwell\Core\Framework\Log\Package;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[Package('checkout')]
class CustomerVatIdentification extends Constraint
{
    final public const string VAT_ID_FORMAT_NOT_CORRECT = '463d3548-1caf-11eb-adc1-0242ac120002';

    protected const array ERROR_NAMES = [
        self::VAT_ID_FORMAT_NOT_CORRECT => 'VAT_ID_FORMAT_NOT_CORRECT',
    ];

    public string $message;

    public string $countryId;

    public bool $shouldCheck;

    #[HasNamedArguments]
    public function __construct(
        string $countryId,
        bool $shouldCheck = false,
        string $message = 'The format of vatId {{ vatId }} is not correct.'
    ) {
        parent::__construct();

        $this->countryId = $countryId;
        $this->shouldCheck = $shouldCheck;
        $this->message = $message;
    }
}
