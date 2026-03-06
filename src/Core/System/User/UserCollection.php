<?php declare(strict_types=1);

namespace Shopwell\Core\System\User;

use Shopwell\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopwell\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<UserEntity>
 */
#[Package('fundamentals@framework')]
class UserCollection extends EntityCollection
{
    /**
     * @return array<string, string>
     */
    public function getLocaleIds(): array
    {
        return $this->fmap(fn (UserEntity $user) => $user->getLocaleId());
    }

    public function filterByLocaleId(string $id): self
    {
        return $this->filter(fn (UserEntity $user) => $user->getLocaleId() === $id);
    }

    public function getApiAlias(): string
    {
        return 'user_collection';
    }

    protected function getExpectedClass(): string
    {
        return UserEntity::class;
    }
}
