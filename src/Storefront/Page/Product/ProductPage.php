<?php declare(strict_types=1);

namespace Shopwell\Storefront\Page\Product;

use Shopwell\Core\Content\Product\ProductDefinition;
use Shopwell\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopwell\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionCollection;
use Shopwell\Core\Content\Property\PropertyGroupCollection;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Storefront\Page\Page;

#[Package('framework')]
class ProductPage extends Page
{
    protected SalesChannelProductEntity $product;

    protected ?string $navigationId = null;

    protected PropertyGroupCollection $configuratorSettings;

    protected PropertyGroupOptionCollection $selectedOptions;

    public function getProduct(): SalesChannelProductEntity
    {
        return $this->product;
    }

    public function setProduct(SalesChannelProductEntity $product): void
    {
        $this->product = $product;
    }

    public function getNavigationId(): ?string
    {
        return $this->navigationId;
    }

    public function setNavigationId(?string $navigationId): void
    {
        $this->navigationId = $navigationId;
    }

    public function getConfiguratorSettings(): PropertyGroupCollection
    {
        return $this->configuratorSettings;
    }

    public function setConfiguratorSettings(PropertyGroupCollection $configuratorSettings): void
    {
        $this->configuratorSettings = $configuratorSettings;
    }

    public function getSelectedOptions(): PropertyGroupOptionCollection
    {
        return $this->selectedOptions;
    }

    public function setSelectedOptions(PropertyGroupOptionCollection $selectedOptions): void
    {
        $this->selectedOptions = $selectedOptions;
    }

    public function getEntityName(): string
    {
        return ProductDefinition::ENTITY_NAME;
    }
}
