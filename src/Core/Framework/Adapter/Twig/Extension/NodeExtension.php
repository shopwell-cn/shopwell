<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Adapter\Twig\Extension;

use Shopwell\Core\Framework\Adapter\Twig\TemplateFinder;
use Shopwell\Core\Framework\Adapter\Twig\TemplateScopeDetector;
use Shopwell\Core\Framework\Adapter\Twig\TokenParser\ExtendsTokenParser;
use Shopwell\Core\Framework\Adapter\Twig\TokenParser\IncludeTokenParser;
use Shopwell\Core\Framework\Adapter\Twig\TokenParser\ReturnNodeTokenParser;
use Shopwell\Core\Framework\Log\Package;
use Twig\Extension\AbstractExtension;
use Twig\TokenParser\TokenParserInterface;

#[Package('framework')]
class NodeExtension extends AbstractExtension
{
    /**
     * @internal
     *
     * @deprecated tag:v6.8.0  - replace TemplateFinder with TemplateFinderInterface
     */
    public function __construct(
        private readonly TemplateFinder $finder,
        private readonly TemplateScopeDetector $templateScopeDetector,
    ) {
    }

    /**
     * @return TokenParserInterface[]
     */
    public function getTokenParsers(): array
    {
        return [
            new ExtendsTokenParser($this->finder, $this->templateScopeDetector),
            new IncludeTokenParser($this->finder),
            new ReturnNodeTokenParser(),
        ];
    }

    public function getFinder(): TemplateFinder
    {
        return $this->finder;
    }
}
