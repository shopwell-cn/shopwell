<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\DataAbstractionLayer\Search\Term;

use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\DataAbstractionLayer\Search\SearchConfigLoader;
use Shopwell\Core\Framework\Log\Package;

#[Package('framework')]
class SearchTermInterpreter
{
    /**
     * @internal
     */
    public function __construct(
        private readonly TokenizerInterface $tokenizer,
        private readonly SearchConfigLoader $configLoader
    ) {
    }

    public function interpret(string $term, Context $context): SearchPattern
    {
        $config = $this->configLoader->load($context);

        /** @phpstan-ignore arguments.count (This ignore should be removed when the deprecated method signature is updated) */
        $terms = $this->tokenizer->tokenize($term, $config[0]['min_search_length'] ?? null);

        $pattern = new SearchPattern(new SearchTerm($term));

        if (\count($terms) === 1) {
            return $pattern;
        }

        foreach ($terms as $part) {
            $percent = mb_strlen($part) / mb_strlen($term);
            $pattern->addTerm(new SearchTerm($part, $percent));
        }

        return $pattern;
    }
}
