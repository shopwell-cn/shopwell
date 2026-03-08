<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Adapter\Twig\Filter;

use Shopwell\Core\Framework\Adapter\AdapterException;
use Shopwell\Core\Framework\Context;
use Shopwell\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\System\Currency\CurrencyFormatter;
use Shopwell\Core\System\SalesChannel\SalesChannelContext;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * @internal
 */
#[Package('framework')]
class CurrencyFilter extends AbstractExtension
{
    /**
     * @internal
     */
    public function __construct(private readonly CurrencyFormatter $currencyFormatter)
    {
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('currency', $this->formatCurrency(...), ['needs_context' => true]),
        ];
    }

    /**
     * @param array<string, mixed> $twigContext
     *
     * @throws InconsistentCriteriaIdsException
     */
    public function formatCurrency(array $twigContext, float $price, ?string $currencyIsoCode = null, ?string $languageId = null, ?int $decimals = null): float|string
    {
        if (!\array_key_exists('context', $twigContext)
            || (
                !$twigContext['context'] instanceof Context
                && !$twigContext['context'] instanceof SalesChannelContext
            )
        ) {
            if (isset($twigContext['testMode']) && $twigContext['testMode'] === true) {
                return $price;
            }

            throw AdapterException::currencyFilterMissingContext();
        }

        if (!$currencyIsoCode && $twigContext['context'] instanceof SalesChannelContext) {
            $currencyIsoCode = $twigContext['context']->getCurrency()->isoCode;
        }

        if (!$currencyIsoCode) {
            if (isset($twigContext['testMode']) && $twigContext['testMode'] === true) {
                return $price;
            }

            throw AdapterException::currencyFilterMissingIsoCode();
        }

        if ($twigContext['context'] instanceof Context) {
            $context = $twigContext['context'];
        } else {
            $context = $twigContext['context']->getContext();
        }

        if ($languageId === null) {
            $languageId = $context->getLanguageId();
        }

        return $this->currencyFormatter->formatCurrencyByLanguage($price, $currencyIsoCode, $languageId, $context, $decimals);
    }
}
