<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\DataAbstractionLayer\Event;

use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityWriteResult;
use Shopwell\Core\Framework\Event\GenericEvent;
use Shopwell\Core\Framework\Event\NestedEvent;
use Shopwell\Core\Framework\Log\Package;

/**
 * @template IDStructure of string|array<string, string> = string
 */
#[Package('framework')]
class EntityWrittenEvent extends NestedEvent implements GenericEvent
{
    /**
     * @var list<IDStructure>|null
     */
    protected ?array $ids = null;

    /**
     * @var list<array<string, mixed>>|null
     */
    protected ?array $payloads = null;

    protected string $name;

    /**
     * @param list<EntityWriteResult<IDStructure>> $writeResults
     * @param array<mixed> $errors
     */
    public function __construct(
        protected string $entityName,
        protected array $writeResults,
        protected Context $context,
        protected array $errors = []
    ) {
        $this->name = $this->entityName . '.written';
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    /**
     * @return array<mixed>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return list<IDStructure>
     */
    public function getIds(): array
    {
        if ($this->ids === null) {
            $this->ids = [];
            foreach ($this->writeResults as $entityWriteResult) {
                $this->ids[] = $entityWriteResult->getPrimaryKey();
            }
        }

        return $this->ids;
    }

    public function getEntityName(): string
    {
        return $this->entityName;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getPayloads(): array
    {
        if ($this->payloads === null) {
            $this->payloads = [];
            foreach ($this->writeResults as $entityWriteResult) {
                $this->payloads[] = $entityWriteResult->getPayload();
            }
        }

        return $this->payloads;
    }

    /**
     * @return list<EntityWriteResult<IDStructure>>
     */
    public function getWriteResults(): array
    {
        return $this->writeResults;
    }
}
