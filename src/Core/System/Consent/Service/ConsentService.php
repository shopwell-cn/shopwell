<?php declare(strict_types=1);

namespace Shopwell\Core\System\Consent\Service;

use Psr\EventDispatcher\EventDispatcherInterface;
use Shopwell\Core\Framework\Api\Context\AdminApiSource;
use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\System\Consent\ConsentDefinition;
use Shopwell\Core\System\Consent\ConsentException;
use Shopwell\Core\System\Consent\ConsentRepository;
use Shopwell\Core\System\Consent\ConsentScope;
use Shopwell\Core\System\Consent\ConsentStatus;
use Shopwell\Core\System\Consent\DTO\ConsentState;
use Shopwell\Core\System\Consent\Event\ConsentAcceptedEvent;
use Shopwell\Core\System\Consent\Event\ConsentRevokedEvent;
use Symfony\Contracts\Service\ResetInterface;

/**
 * @internal
 */
#[Package('data-services')]
class ConsentService implements ResetInterface
{
    /**
     * @var array<string, ConsentScope>
     */
    private array $consentScopes;

    /**
     * @var array<string, ConsentDefinition>
     */
    private array $consentDefinitions;

    /**
     * @var array<string, ConsentState>
     */
    private ?array $states = null;

    /**
     * @param iterable<ConsentScope> $consentScopes
     * @param iterable<ConsentDefinition> $consentDefinitions
     */
    public function __construct(
        iterable $consentScopes,
        iterable $consentDefinitions,
        private readonly ConsentRepository $consentRepository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
        $scopes = [];
        foreach ($consentScopes as $scope) {
            $scopes[$scope->getName()] = $scope;
        }
        $this->consentScopes = $scopes;

        $definitions = [];
        foreach ($consentDefinitions as $definition) {
            $definitions[$definition->getName()] = $definition;
        }
        $this->consentDefinitions = $definitions;
    }

    /**
     * @return array<ConsentState>
     */
    public function list(Context $context): array
    {
        $states = $this->fetchStates($context);

        return array_map(function (ConsentDefinition $consent) use ($context, $states) {
            $key = $this->key($consent, $context);

            return $states[$key] ?? new ConsentState(
                name: $consent->getName(),
                scopeName: $consent->getScopeName(),
                identifier: $this->getScope($consent)->resolveIdentifier($context),
                status: ConsentStatus::UNSET,
                actor: null,
                updatedAt: null,
            );
        }, $this->consentDefinitions);
    }

    public function getConsentState(string $name, Context $context): ConsentState
    {
        $consent = $this->getConsentDefinition($name);
        $key = $this->key($consent, $context);

        $states = $this->fetchStates($context);
        if (isset($states[$key])) {
            return $states[$key];
        }

        return new ConsentState(
            name: $consent->getName(),
            scopeName: $consent->getScopeName(),
            identifier: $this->getScope($consent)->resolveIdentifier($context),
            status: ConsentStatus::UNSET,
            actor: null,
            updatedAt: null,
        );
    }

    public function acceptConsent(string $name, Context $context): ConsentState
    {
        $updatedState = $this->updateState($name, ConsentStatus::ACCEPTED, $context);

        \assert(\is_string($updatedState->actor));
        $this->eventDispatcher->dispatch(new ConsentAcceptedEvent($updatedState->name, $updatedState->scopeName, $updatedState->identifier, $updatedState->actor));

        return $updatedState;
    }

    public function revokeConsent(string $name, Context $context): ConsentState
    {
        $updatedState = $this->updateState($name, ConsentStatus::REVOKED, $context);

        \assert(\is_string($updatedState->actor));
        $this->eventDispatcher->dispatch(new ConsentRevokedEvent($updatedState->name, $updatedState->scopeName, $updatedState->identifier, $updatedState->actor));

        return $updatedState;
    }

    public function reset(): void
    {
        $this->invalidateState();
    }

    private function getConsentDefinition(string $name): ConsentDefinition
    {
        if (!isset($this->consentDefinitions[$name])) {
            throw ConsentException::notFound($name);
        }

        return $this->consentDefinitions[$name];
    }

    /**
     * @return array<string, ConsentState>
     */
    private function fetchStates(Context $context): array
    {
        if ($this->states !== null) {
            return $this->states;
        }

        $states = [];

        foreach ($this->consentRepository->fetchAllConsentStates() as $record) {
            $state = ConsentState::fromDefinitionAndRecord(
                $this->getConsentDefinition($record->name),
                $record
            );

            $states[$this->key($state, $context)] = $state;
        }

        return $this->states = $states;
    }

    private function key(ConsentState|ConsentDefinition $consent, Context $context): string
    {
        if ($consent instanceof ConsentDefinition) {
            $scopeIdentifier = $this->getScope($consent)->resolveIdentifier($context);

            return $consent->getName() . ':' . $consent->getScopeName() . ':' . $scopeIdentifier;
        }

        // $consent is instance of ConsentState
        return $consent->name . ':' . $consent->scopeName . ':' . $consent->identifier;
    }

    private function invalidateState(): void
    {
        $this->states = null;
    }

    private function getScope(ConsentDefinition $consent): ConsentScope
    {
        if (!isset($this->consentScopes[$consent->getScopeName()])) {
            throw ConsentException::invalidScope($consent->getScopeName());
        }

        return $this->consentScopes[$consent->getScopeName()];
    }

    private function updateState(string $name, ConsentStatus $status, Context $context): ConsentState
    {
        $consent = $this->getConsentDefinition($name);

        $this->validatePermissions($context, $consent);

        $key = $this->key($consent, $context);

        $states = $this->fetchStates($context);
        $stored = $states[$key] ?? null;

        if ($stored !== null) {
            if ($stored->status === $status) {
                return $stored;
            }

            if ($stored->status === ConsentStatus::DECLINED && $status === ConsentStatus::REVOKED) {
                return $stored;
            }
        }

        $scope = $this->getScope($consent);

        $this->consentRepository->updateConsentState(
            $consent,
            $scope->resolveIdentifier($context),
            $status,
            $scope->resolveActorIdentifier($context)
        );

        $this->invalidateState();

        return $this->getConsentState($name, $context);
    }

    private function validatePermissions(Context $context, ConsentDefinition $consent): void
    {
        $source = $context->getSource();

        \assert($source instanceof AdminApiSource);

        if ($source->isAdmin()) {
            return;
        }

        $missingPermissions = [];
        foreach ($consent->getRequiredPermissions() as $permission) {
            if (!$source->isAllowed($permission)) {
                $missingPermissions[] = $permission;
            }
        }

        if ($missingPermissions !== []) {
            throw ConsentException::insufficientPermissions($consent->getName(), $missingPermissions);
        }
    }
}
