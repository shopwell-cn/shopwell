<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\App\ShopIdChangeResolver;

use Shopwell\Core\Framework\App\AppCollection;
use Shopwell\Core\Framework\App\AppEntity;
use Shopwell\Core\Framework\App\Lifecycle\AppSecretRotationService;
use Shopwell\Core\Framework\App\Manifest\Manifest;
use Shopwell\Core\Framework\App\Source\SourceResolver;
use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopwell\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('framework')]
abstract class AbstractShopIdChangeStrategy
{
    /**
     * @param EntityRepository<AppCollection> $appRepository
     */
    public function __construct(
        private readonly SourceResolver $sourceResolver,
        private readonly EntityRepository $appRepository,
        private readonly AppSecretRotationService $appSecretRotationService
    ) {
    }

    abstract public function getName(): string;

    /**
     * @return string the description of the strategy used to explain what the strategy does in CLI and API
     *
     * Note: in the administration we have separate snippets for this to localize the description, keep the descriptions in sync
     * `sw-app.component.sw-app-shop-id-change-modal.strategies.${strategy-name}.description`
     */
    abstract public function getDescription(): string;

    abstract public function resolve(Context $context): void;

    abstract public function getDecorated(): self;

    /**
     * @param callable(Manifest, AppEntity, Context): void $callback
     */
    protected function forEachInstalledApp(Context $context, callable $callback): void
    {
        $apps = $this->appRepository->search(new Criteria(), $context);

        foreach ($apps as $app) {
            $fs = $this->sourceResolver->filesystemForApp($app);
            $path = $fs->hasFile('manifest.local.xml') ? 'manifest.local.xml' : 'manifest.xml';
            $manifest = Manifest::createFromXmlFile($fs->path($path));

            if (!$manifest->getSetup()) {
                continue;
            }

            $callback($manifest, $app, $context);
        }
    }

    protected function reRegisterApp(Manifest $manifest, AppEntity $app, Context $context): void
    {
        $this->appSecretRotationService->rotateNow($app->getId(), $context, AppSecretRotationService::TRIGGER_SHOP_MOVE);
    }
}
