<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\App;

use Shopwell\Core\Framework\App\Event\AppActivatedEvent;
use Shopwell\Core\Framework\App\Event\AppDeactivatedEvent;
use Shopwell\Core\Framework\App\Event\Hooks\AppActivatedHook;
use Shopwell\Core\Framework\App\Event\Hooks\AppDeactivatedHook;
use Shopwell\Core\Framework\App\Lifecycle\Persister\FlowEventPersister;
use Shopwell\Core\Framework\App\Lifecycle\Persister\RuleConditionPersister;
use Shopwell\Core\Framework\App\Lifecycle\Persister\ScriptPersister;
use Shopwell\Core\Framework\App\Payment\PaymentMethodStateService;
use Shopwell\Core\Framework\App\Template\TemplateStateService;
use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Script\Execution\ScriptExecutor;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @internal only for use by the app-system
 */
#[Package('framework')]
class AppStateService
{
    /**
     * @param EntityRepository<AppCollection> $appRepo
     */
    public function __construct(
        private readonly EntityRepository $appRepo,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ActiveAppsLoader $activeAppsLoader,
        private readonly TemplateStateService $templateStateService,
        private readonly ScriptPersister $scriptPersister,
        private readonly PaymentMethodStateService $paymentMethodStateService,
        private readonly ScriptExecutor $scriptExecutor,
        private readonly RuleConditionPersister $ruleConditionPersister,
        private readonly FlowEventPersister $flowEventPersister
    ) {
    }

    public function activateApp(string $appId, Context $context): void
    {
        $app = $this->appRepo->search(new Criteria([$appId]), $context)->getEntities()->first();

        if ($app === null) {
            throw AppException::notFound($appId);
        }
        if ($app->isActive()) {
            return;
        }

        $this->appRepo->update([['id' => $appId, 'active' => true]], $context);
        $this->templateStateService->activateAppTemplates($appId, $context);
        $this->scriptPersister->activateAppScripts($appId, $context);
        $this->paymentMethodStateService->activatePaymentMethods($appId, $context);
        $this->ruleConditionPersister->activateConditionScripts($appId, $context);
        $this->activeAppsLoader->reset();
        // manually set active flag to true, so we don't need to re-fetch the app from DB
        $app->setActive(true);

        $event = new AppActivatedEvent($app, $context);
        $this->eventDispatcher->dispatch($event);
        $this->scriptExecutor->execute(new AppActivatedHook($event));
    }

    public function deactivateApp(string $appId, Context $context, bool $deactivateForDeletion = false): void
    {
        $app = $this->appRepo->search(new Criteria([$appId]), $context)->getEntities()->first();

        if ($app === null) {
            throw AppException::notFound($appId);
        }
        if (!$app->isActive()) {
            return;
        }
        if (!$deactivateForDeletion && !$app->getAllowDisable()) {
            throw AppException::restrictDeletePreventsDeactivation($app->getName());
        }

        // throw event before deactivating app in db as theme configs from the app need to be removed beforehand
        $event = new AppDeactivatedEvent($app, $context);
        $this->eventDispatcher->dispatch($event);
        $this->scriptExecutor->execute(new AppDeactivatedHook($event));

        $this->appRepo->update([['id' => $appId, 'active' => false]], $context);
        $this->templateStateService->deactivateAppTemplates($appId, $context);
        $this->scriptPersister->deactivateAppScripts($appId, $context);
        $this->paymentMethodStateService->deactivatePaymentMethods($appId, $context);
        $this->ruleConditionPersister->deactivateConditionScripts($appId, $context);
        $this->flowEventPersister->deactivateFlow($appId);
        // reset only after new state is in the DB
        $this->activeAppsLoader->reset();
    }
}
