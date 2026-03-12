<?php declare(strict_types=1);

namespace Shopwell\Storefront\Page\Robots\Struct;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Struct\Struct;
use Shopwell\Storefront\Page\Robots\Parser\ParsedRobots;

#[Package('framework')]
class DomainRuleStruct extends Struct
{
    /**
     * @var list<RobotsDirective>
     */
    private array $directives = [];

    public function __construct(ParsedRobots $parsed, private readonly string $basePath)
    {
        $this->initializeFromParsed($parsed);
    }

    /**
     * @return list<RobotsDirective>
     */
    public function getDirectives(): array
    {
        return $this->directives;
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    private function initializeFromParsed(ParsedRobots $parsed): void
    {
        $allDirectives = array_merge(
            $parsed->orphanedPathDirectives,
            ...array_map(static fn (RobotsUserAgentBlock $block) => $block->getPathDirectives(), $parsed->userAgentBlocks)
        );

        foreach ($allDirectives as $directive) {
            $directiveWithPath = $directive->withBasePath($this->basePath);
            $this->directives[] = $directiveWithPath;
        }
    }
}
