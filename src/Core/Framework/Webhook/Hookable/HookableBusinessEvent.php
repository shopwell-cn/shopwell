<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Webhook\Hookable;

use Shopwell\Core\Framework\Api\Acl\Role\AclRoleDefinition;
use Shopwell\Core\Framework\App\AppEntity;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopwell\Core\Framework\Event\EventData\ArrayType;
use Shopwell\Core\Framework\Event\EventData\EntityCollectionType;
use Shopwell\Core\Framework\Event\EventData\EntityType;
use Shopwell\Core\Framework\Event\EventData\ObjectType;
use Shopwell\Core\Framework\Event\FlowEventAware;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Webhook\AclPrivilegeCollection;
use Shopwell\Core\Framework\Webhook\BusinessEventEncoder;
use Shopwell\Core\Framework\Webhook\Hookable;

/**
 * @internal
 */
#[Package('framework')]
class HookableBusinessEvent implements Hookable
{
    private function __construct(
        private readonly FlowEventAware $flowEventAware,
        private readonly BusinessEventEncoder $businessEventEncoder
    ) {
    }

    public static function fromBusinessEvent(
        FlowEventAware $flowEventAware,
        BusinessEventEncoder $businessEventEncoder
    ): self {
        return new self($flowEventAware, $businessEventEncoder);
    }

    public function getName(): string
    {
        return $this->flowEventAware->getName();
    }

    public function getWebhookPayload(?AppEntity $app = null): array
    {
        return $this->businessEventEncoder->encode($this->flowEventAware);
    }

    public function isAllowed(string $appId, AclPrivilegeCollection $permissions): bool
    {
        foreach ($this->flowEventAware->getAvailableData()->toArray() as $dataType) {
            if (!$this->checkPermissionsForDataType($dataType, $permissions)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<mixed> $dataType
     */
    private function checkPermissionsForDataType(array $dataType, AclPrivilegeCollection $permissions): bool
    {
        $type = $dataType['type'];
        $data = $dataType['data'];
        if ($type === ObjectType::TYPE && \is_array($data) && $data !== []) {
            foreach ($data as $nested) {
                if (!$this->checkPermissionsForDataType($nested, $permissions)) {
                    return false;
                }
            }
        }

        if ($type === ArrayType::TYPE && $dataType['of'] && !$this->checkPermissionsForDataType($dataType['of'], $permissions)) {
            return false;
        }

        if ($type === EntityType::TYPE || $type === EntityCollectionType::TYPE) {
            /** @var EntityDefinition $definition */
            $definition = new $dataType['entityClass']();
            if (!$permissions->isAllowed($definition->getEntityName(), AclRoleDefinition::PRIVILEGE_READ)) {
                return false;
            }
        }

        return true;
    }
}
