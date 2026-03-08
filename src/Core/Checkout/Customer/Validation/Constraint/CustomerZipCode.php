<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Customer\Validation\Constraint;

use Shopwell\Core\Framework\Log\Package;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;

#[Package('checkout')]
class CustomerZipCode extends Constraint
{
    final public const string ZIP_CODE_INVALID = 'ZIP_CODE_INVALID';

    protected const array ERROR_NAMES = [
        NotBlank::IS_BLANK_ERROR => 'IS_BLANK_ERROR',
        self::ZIP_CODE_INVALID => 'ZIP_CODE_INVALID',
    ];

    public bool $caseSensitiveCheck;

    public ?string $countryId;

    public string $message;

    public string $messageRequired;

    #[HasNamedArguments]
    public function __construct(
        bool $caseSensitiveCheck = true,
        ?string $countryId = null,
        string $message = 'This value is not a valid ZIP code for country {{ iso }}',
        string $messageRequired = 'Postal code is required for that country'
    ) {
        parent::__construct();

        $this->caseSensitiveCheck = $caseSensitiveCheck;
        $this->countryId = $countryId;
        $this->message = $message;
        $this->messageRequired = $messageRequired;
    }
}
