<?php declare(strict_types=1);

namespace Shopwell\Storefront\Pagelet\Header;

use Shopwell\Core\Content\Category\Tree\Tree;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\System\Currency\CurrencyCollection;
use Shopwell\Core\System\Language\LanguageCollection;
use Shopwell\Storefront\Pagelet\NavigationPagelet;

#[Package('framework')]
class HeaderPagelet extends NavigationPagelet
{
    /**
     * @internal
     */
    public function __construct(
        Tree $navigation,
        protected LanguageCollection $languages,
        protected CurrencyCollection $currencies,
    ) {
        parent::__construct($navigation);
    }

    public function getLanguages(): LanguageCollection
    {
        return $this->languages;
    }

    public function getCurrencies(): CurrencyCollection
    {
        return $this->currencies;
    }
}
