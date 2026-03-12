<?php declare(strict_types=1);

namespace Shopwell\Core\Content\Flow;

use Shopwell\Core\Content\Flow\Aggregate\FlowSequence\FlowSequenceCollection;
use Shopwell\Core\Content\Flow\Dispatching\Struct\Flow;
use Shopwell\Core\Framework\App\Aggregate\FlowEvent\AppFlowEventEntity;
use Shopwell\Core\Framework\DataAbstractionLayer\Entity;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopwell\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopwell\Core\Framework\Log\Package;

#[Package('after-sales')]
class FlowEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;

    public string $name;

    public string $eventName;

    public string $description;

    public bool $active;

    public int $priority;

    public ?string $appFlowEventId = null;

    public ?AppFlowEventEntity $appFlowEvent = null;

    public bool $invalid;

    public ?FlowSequenceCollection $sequences = null;

    /**
     * @internal
     */
    protected string|Flow|null $payload = null;

    /**
     * @internal
     *
     * @return string|Flow|null
     */
    public function getPayload()
    {
        $this->checkIfPropertyAccessIsAllowed('payload');

        return $this->payload;
    }

    /**
     * @internal
     *
     * @param string|Flow|null $payload
     */
    public function setPayload($payload): void
    {
        $this->payload = $payload;
    }
}
