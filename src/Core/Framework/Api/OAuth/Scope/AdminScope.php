<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Api\OAuth\Scope;

use League\OAuth2\Server\Entities\ScopeEntityInterface;
use Shopwell\Core\Framework\Log\Package;

#[Package('framework')]
class AdminScope implements ScopeEntityInterface
{
    final public const IDENTIFIER = 'admin';

    public function getIdentifier(): string
    {
        return self::IDENTIFIER;
    }

    public function jsonSerialize(): mixed
    {
        return self::IDENTIFIER;
    }
}
