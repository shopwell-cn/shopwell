<?php declare(strict_types=1);

namespace Shopwell\Core\DevOps\StaticAnalyze\PHPStan\Rules\Tests;

use PHPStan\Reflection\ClassReflection;
use PHPUnit\Framework\TestCase;
use Shopwell\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('framework')]
class TestRuleHelper
{
    public static function isTestClass(TestReflectionClassInterface|ClassReflection $class): bool
    {
        foreach ($class->getParents() as $parent) {
            if ($parent->getName() === TestCase::class) {
                return true;
            }
        }

        return false;
    }

    public static function isUnitTestClass(TestReflectionClassInterface|ClassReflection $class): bool
    {
        if (!static::isTestClass($class)) {
            return false;
        }

        $unitTestNamespaces = [
            'Shopwell\\Tests\\Unit\\',
            'Shopwell\\Tests\\Migration\\',

            'Shopwell\\Commercial\\Tests\\Unit\\',
            'Shopwell\\Commercial\\Migration\\Test\\',

            'Swag\\SaasRufus\\Test\\Migration\\',
            'Swag\\SaasRufus\\Tests\\Unit\\',
        ];

        foreach ($unitTestNamespaces as $unitTestNamespace) {
            if (\str_contains($class->getName(), $unitTestNamespace)) {
                return true;
            }
        }

        return false;
    }
}
