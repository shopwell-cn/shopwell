<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Adapter\Twig\NamespaceHierarchy;

use Shopwell\Core\Framework\Log\Package;

#[Package('framework')]
class NamespaceHierarchyBuilder
{
    /**
     * @internal
     *
     * @param TemplateNamespaceHierarchyBuilderInterface[] $namespaceHierarchyBuilders
     */
    public function __construct(private readonly iterable $namespaceHierarchyBuilders)
    {
    }

    /**
     * @return array<string, int>
     */
    public function buildHierarchy(): array
    {
        $hierarchy = [];

        foreach ($this->namespaceHierarchyBuilders as $hierarchyBuilder) {
            $hierarchy = $hierarchyBuilder->buildNamespaceHierarchy($hierarchy);
        }

        return $hierarchy;
    }
}
