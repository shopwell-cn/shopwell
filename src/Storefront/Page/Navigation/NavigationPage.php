<?php declare(strict_types=1);

namespace Shopwell\Storefront\Page\Navigation;

use Shopwell\Core\Content\Category\CategoryDefinition;
use Shopwell\Core\Content\Category\CategoryEntity;
use Shopwell\Core\Content\Cms\CmsPageEntity;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Storefront\Page\Page;

#[Package('framework')]
class NavigationPage extends Page
{
    protected ?CmsPageEntity $cmsPage = null;

    protected ?CategoryEntity $category = null;

    protected ?string $navigationId = null;

    public function getCmsPage(): ?CmsPageEntity
    {
        return $this->cmsPage;
    }

    public function setCmsPage(CmsPageEntity $cmsPage): void
    {
        $this->cmsPage = $cmsPage;
    }

    public function getNavigationId(): ?string
    {
        return $this->navigationId;
    }

    public function setNavigationId(?string $navigationId): void
    {
        $this->navigationId = $navigationId;
    }

    public function getCategory(): ?CategoryEntity
    {
        return $this->category;
    }

    public function setCategory(?CategoryEntity $category): void
    {
        $this->category = $category;
    }

    public function getEntityName(): string
    {
        return CategoryDefinition::ENTITY_NAME;
    }
}
