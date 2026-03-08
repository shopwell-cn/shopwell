<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\DataAbstractionLayer\Validation;

use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopwell\Core\Framework\Log\Package;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[Package('framework')]
class EntityExists extends Constraint
{
    final public const string ENTITY_DOES_NOT_EXISTS = 'f1e5c873-5baf-4d5b-8ab7-e422bfce91f1';

    protected const array ERROR_NAMES = [
        self::ENTITY_DOES_NOT_EXISTS => 'ENTITY_DOES_NOT_EXISTS',
    ];

    public string $message;

    public string $entity;

    public Context $context;

    public Criteria $criteria;

    public string $primaryProperty;

    /**
     * @param non-empty-string $entity
     */
    #[HasNamedArguments]
    public function __construct(
        string $entity,
        Context $context,
        string $primaryProperty = 'id',
        ?Criteria $criteria = null,
        string $message = 'The {{ entity }} entity with {{ primaryProperty }} {{ id }} does not exist.'
    ) {
        parent::__construct();

        $this->entity = $entity;
        $this->context = $context;
        $this->primaryProperty = $primaryProperty;
        $this->criteria = $criteria ?? new Criteria();
        $this->message = $message;
    }
}
