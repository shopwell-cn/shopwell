<?php declare(strict_types=1);

namespace Shopwell\Core\Service\Requirement;

use Doctrine\DBAL\Connection;
use Shopwell\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('framework')]
class ShopwellAccountRequirement implements ServiceRequirement
{
    public const NAME = 'shopwell_account';

    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public static function getName(): string
    {
        return self::NAME;
    }

    public function isSatisfied(): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT 1 FROM `user` WHERE `store_token` IS NOT NULL LIMIT 1'
        );
    }
}
