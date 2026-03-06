<?php declare(strict_types=1);

namespace Shopwell\Storefront\Framework\Twig;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Storefront\Framework\Twig\TokenParser\IconTokenParser;
use Twig\Extension\AbstractExtension;

#[Package('framework')]
class IconExtension extends AbstractExtension
{
    /**
     * @internal
     */
    public function __construct()
    {
    }

    public function getTokenParsers(): array
    {
        return [
            new IconTokenParser(),
        ];
    }
}
