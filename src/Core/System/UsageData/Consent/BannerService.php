<?php declare(strict_types=1);

namespace Shopwell\Core\System\UsageData\Consent;

use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Uuid\Uuid;
use Shopwell\Core\System\User\Aggregate\UserConfig\UserConfigCollection;

/**
 * @internal
 */
#[Package('data-services')]
class BannerService
{
    public const USER_CONFIG_KEY_HIDE_CONSENT_BANNER = 'core.usageData.hideConsentBanner';

    /**
     * @param EntityRepository<UserConfigCollection> $userConfigRepository
     */
    public function __construct(private readonly EntityRepository $userConfigRepository)
    {
    }

    public function hideConsentBannerForUser(string $userId, Context $context): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('userId', $userId));
        $criteria->addFilter(new EqualsFilter('key', self::USER_CONFIG_KEY_HIDE_CONSENT_BANNER));

        $userConfigId = $this->userConfigRepository->searchIds($criteria, $context)->firstId();

        $this->userConfigRepository->upsert([
            [
                'id' => $userConfigId ?: Uuid::randomHex(),
                'userId' => $userId,
                'key' => self::USER_CONFIG_KEY_HIDE_CONSENT_BANNER,
                'value' => ['_value' => true],
            ],
        ], $context);
    }

    public function hasUserHiddenConsentBanner(string $userId, Context $context): bool
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('key', self::USER_CONFIG_KEY_HIDE_CONSENT_BANNER));
        $criteria->addFilter(new EqualsFilter('userId', $userId));

        $userConfig = $this->userConfigRepository->search($criteria, $context)->getEntities()->first();
        if ($userConfig === null) {
            return false;
        }

        return $userConfig->getValue()['_value'] ?? false;
    }

    public function resetIsBannerHiddenForAllUsers(): void
    {
        $context = Context::createDefaultContext();

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('key', self::USER_CONFIG_KEY_HIDE_CONSENT_BANNER));

        $userConfigs = $this->userConfigRepository->search($criteria, $context);
        if ($userConfigs->getTotal() === 0) {
            return;
        }

        $updates = [];

        foreach ($userConfigs->getEntities() as $userConfig) {
            $updates[] = [
                'id' => $userConfig->getId(),
                'userId' => $userConfig->getUserId(),
                'key' => self::USER_CONFIG_KEY_HIDE_CONSENT_BANNER,
                'value' => ['_value' => false],
            ];
        }

        $this->userConfigRepository->upsert($updates, $context);
    }
}
