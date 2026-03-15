<?php declare(strict_types=1);

namespace Shopwell\Core\Test;

use Shopwell\Core\Framework\Log\Package;

/**
 * @internal
 * This class contains some defaults for test case
 */
#[Package('framework')]
class TestDefaults
{
    final public const string SALES_CHANNEL = '98432def39fc4624b33213a56b8c944d';
    final public const string FALLBACK_CUSTOMER_GROUP = 'cfbd5018d38d41d8adca10d94fc8bdd6';
    // use pre-hashed password, so we don't need to hash in every test, password is `shopwell`
    final public const string HASHED_PASSWORD = '$2y$12$zC0u0L2yuWqLpqE1OYL2TuFsuoJSJ1oH00xNlnG91WwJRK4okkZei';
}
