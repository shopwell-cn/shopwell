<?php declare(strict_types=1);

namespace Shopwell\Core\Checkout\Customer;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Token\RegisteredClaims;
use Shopwell\Core\Checkout\Customer\Struct\ImitateCustomerToken;
use Shopwell\Core\Framework\JWT\SalesChannel\JWTGenerator;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Validation\DataValidationDefinition;
use Shopwell\Core\Framework\Validation\DataValidator;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * @extends JWTGenerator<ImitateCustomerToken>
 */
#[Package('checkout')]
class ImitateCustomerTokenGenerator extends JWTGenerator
{
    public const string OPENSSL_CIPHER_ALGORITHM = 'aes-256-cbc';

    /**
     * @internal
     */
    public function __construct(
        private readonly string $appSecret,
        private readonly Configuration $configuration,
        private readonly DataValidator $validator,
    ) {
        parent::__construct($this->configuration, $this->validator);
    }

    protected function getJWTStructClass(): string
    {
        return ImitateCustomerToken::class;
    }

    protected function getStructConstraints(): DataValidationDefinition
    {
        $definition = parent::getStructConstraints();
        $definition->add(RegisteredClaims::ISSUER, new NotBlank(), new NotNull());

        return $definition;
    }

    private function encrypt(string $token): string
    {
        $iv = openssl_random_pseudo_bytes((int) openssl_cipher_iv_length(self::OPENSSL_CIPHER_ALGORITHM));
        $encrypted = openssl_encrypt($token, self::OPENSSL_CIPHER_ALGORITHM, $this->appSecret, 0, $iv);

        if ($encrypted === false) {
            throw CustomerException::invalidImitationToken($token);
        }

        return base64_encode($iv . $encrypted);
    }

    private function decrypt(string $token): string
    {
        $data = base64_decode($token, true);

        if ($data === false) {
            throw CustomerException::invalidImitationToken($token);
        }

        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);

        if (\strlen($iv) !== 16) {
            throw CustomerException::invalidImitationToken($token);
        }

        $decrypted = openssl_decrypt($encrypted, self::OPENSSL_CIPHER_ALGORITHM, $this->appSecret, 0, $iv);

        if ($decrypted === false) {
            throw CustomerException::invalidImitationToken($token);
        }

        return $decrypted;
    }
}
