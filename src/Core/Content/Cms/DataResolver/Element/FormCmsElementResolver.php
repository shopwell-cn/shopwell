<?php declare(strict_types=1);

namespace Shopwell\Core\Content\Cms\DataResolver\Element;

use Shopwell\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopwell\Core\Content\Cms\DataResolver\CriteriaCollection;
use Shopwell\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\System\Salutation\SalesChannel\AbstractSalutationRoute;
use Shopwell\Core\System\Salutation\SalutationEntity;
use Symfony\Component\HttpFoundation\Request;

#[Package('discovery')]
class FormCmsElementResolver extends AbstractCmsElementResolver
{
    /**
     * @internal
     */
    public function __construct(private readonly AbstractSalutationRoute $salutationRoute)
    {
    }

    public function getType(): string
    {
        return 'form';
    }

    public function collect(CmsSlotEntity $slot, ResolverContext $resolverContext): ?CriteriaCollection
    {
        return null;
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        $context = $resolverContext->getSalesChannelContext();

        $salutations = $this->salutationRoute->load(new Request(), $context, new Criteria())->getSalutations();

        $salutations->sort(fn (SalutationEntity $a, SalutationEntity $b) => $b->getSalutationKey() <=> $a->getSalutationKey());

        $slot->setData($salutations);
    }
}
