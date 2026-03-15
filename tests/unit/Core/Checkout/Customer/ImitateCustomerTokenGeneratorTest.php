<?php declare(strict_types=1);

namespace Shopwell\Tests\Unit\Core\Checkout\Customer;

use Lcobucci\JWT\Configuration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopwell\Core\Checkout\Customer\CustomerException;
use Shopwell\Core\Checkout\Customer\ImitateCustomerTokenGenerator;
use Shopwell\Core\Checkout\Customer\Struct\ImitateCustomerToken;
use Shopwell\Core\Framework\Api\OAuth\JWTConfigurationFactory;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Validation\DataValidationDefinition;
use Shopwell\Core\Framework\Validation\DataValidator;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;

/**
 * @internal
 */
#[Package('checkout')]
#[CoversClass(ImitateCustomerTokenGenerator::class)]
class ImitateCustomerTokenGeneratorTest extends TestCase
{
    private const string SALES_CHANNEL_ID = '0146543d6a6241718da05d5ee6f6891a';
    private const string CUSTOMER_ID = 'bcf76884cb764eb2b9650bb2fcf1073e';
    private const string USER_ID = 'bcf76884cb764eb2b9650bb2fcf1073f';
    private const string APP_SECRET = 'testAppSecret';

    private ImitateCustomerTokenGenerator $imitateCustomerTokenGenerator;

    private DataValidator&MockObject $dataValidator;

    private Configuration $jwtConfiguration;

    protected function setUp(): void
    {
        $this->dataValidator = $this->createMock(DataValidator::class);
        $this->jwtConfiguration = JWTConfigurationFactory::createJWTConfiguration();

        $this->imitateCustomerTokenGenerator = new ImitateCustomerTokenGenerator(self::APP_SECRET, $this->jwtConfiguration, $this->dataValidator);
    }

    public function testEncodeDecode(): void
    {
        $tokenStruct = new ImitateCustomerToken();
        $tokenStruct->salesChannelId = self::SALES_CHANNEL_ID;
        $tokenStruct->customerId = self::CUSTOMER_ID;
        $tokenStruct->iss = self::USER_ID;
        $token = $this->imitateCustomerTokenGenerator->encode($tokenStruct);

        $decodedToken = $this->imitateCustomerTokenGenerator->decode($token);

        static::assertSame(self::SALES_CHANNEL_ID, $decodedToken->salesChannelId);
        static::assertSame(self::CUSTOMER_ID, $decodedToken->customerId);
        static::assertSame(self::USER_ID, $decodedToken->iss);
    }

    public function testConstraint(): void
    {
        $tokenStruct = new ImitateCustomerToken();
        $token = $this->imitateCustomerTokenGenerator->encode($tokenStruct);

        $this->dataValidator
            ->expects($this->once())
            ->method('validate')
            ->with(static::isArray(), static::callback(static function (DataValidationDefinition $constraints): bool {
                $property = $constraints->getProperty('iss');
                static::assertEquals([new Type('string'), new NotBlank(), new NotNull()], $property);

                return true;
            }));

        $this->imitateCustomerTokenGenerator->decode($token);
    }

    private function encrypt(string $token): string
    {
        $iv = openssl_random_pseudo_bytes((int) openssl_cipher_iv_length(ImitateCustomerTokenGenerator::OPENSSL_CIPHER_ALGORITHM));
        $encrypted = openssl_encrypt($token, ImitateCustomerTokenGenerator::OPENSSL_CIPHER_ALGORITHM, self::APP_SECRET, 0, $iv);

        if ($encrypted === false) {
            throw CustomerException::invalidImitationToken($token);
        }

        return base64_encode($iv . $encrypted);
    }
}
