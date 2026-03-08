<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\DataAbstractionLayer\Validation;

use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopwell\Core\Framework\Log\Package;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[Package('framework')]
class EntityNotExists extends Constraint
{
    final public const string ENTITY_EXISTS = 'fr456trg-r43w-ko87-z54e-de4r5tghzt65';

    protected const array ERROR_NAMES = [
        self::ENTITY_EXISTS => 'ENTITY_EXISTS',
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
        string $message = 'The {{ entity }} entity already exists.'
    ) {
        parent::__construct();

        $this->entity = $entity;
        $this->context = $context;
        $this->primaryProperty = $primaryProperty;
        $this->criteria = $criteria ?? new Criteria();
        $this->message = $message;
    }
}
