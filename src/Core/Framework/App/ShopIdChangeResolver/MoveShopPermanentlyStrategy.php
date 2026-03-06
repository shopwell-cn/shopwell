<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\App\ShopIdChangeResolver;

use Shopwell\Core\Framework\App\AppEntity;
use Shopwell\Core\Framework\App\Exception\ShopIdChangeSuggestedException;
use Shopwell\Core\Framework\App\Lifecycle\Registration\AppRegistrationService;
use Shopwell\Core\Framework\App\Manifest\Manifest;
use Shopwell\Core\Framework\App\ShopId\ShopIdProvider;
use Shopwell\Core\Framework\App\Source\SourceResolver;
use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Plugin\Exception\DecorationPatternException;

/**
 * @internal
 *
 * Resolver used when shop is moved from one URL to another
 * and the shopId (and the data in the app backends associated with it) should be kept
 *
 * Will run through the registration process for all apps again
 * with the new appUrl so the apps can save the new URL and generate new Secrets
 * that way communication from the old shop to the app backend will be blocked in the future
 */
#[Package('framework')]
class MoveShopPermanentlyStrategy extends AbstractShopIdChangeStrategy
{
    final public const STRATEGY_NAME = 'move-shop-permanently';

    public function __construct(
        SourceResolver $sourceResolver,
        EntityRepository $appRepository,
        AppRegistrationService $registrationService,
        private readonly ShopIdProvider $shopIdProvider
    ) {
        parent::__construct($sourceResolver, $appRepository, $registrationService);
    }

    public function getDecorated(): AbstractShopIdChangeStrategy
    {
        throw new DecorationPatternException(self::class);
    }

    public function getName(): string
    {
        return self::STRATEGY_NAME;
    }

    public function getDescription(): string
    {
        return 'This is typically the right option if you have permanently moved your shop to a different infrastructure or new environment. Shopwell will notify apps (i.e. re-register at the app servers) using the same shop identifier and apps remain installed. Your shop will identify as the same shop as before. This means, that this instance will override the app data of the original installation.';
    }

    public function resolve(Context $context): void
    {
        try {
            $this->shopIdProvider->reset();
            $this->shopIdProvider->getShopId();

            // no resolution needed
            return;
        } catch (ShopIdChangeSuggestedException $e) {
            $this->shopIdProvider->regenerateAndSetShopId($e->shopId->id);
        }

        $this->forEachInstalledApp($context, function (Manifest $manifest, AppEntity $app, Context $context): void {
            $this->reRegisterApp($manifest, $app, $context);
        });
    }
}
