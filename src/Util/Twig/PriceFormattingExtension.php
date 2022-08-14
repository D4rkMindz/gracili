<?php

namespace App\Util\Twig;

use App\Type\Price;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Class PriceFormattingExtension
 */
class PriceFormattingExtension extends AbstractExtension
{
    /**
     * Get the filters for this extension
     *
     * @return TwigFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('as_price', [$this, 'asPrice']),
        ];
    }

    /**
     * Format a price as string
     *
     * @param int         $price
     * @param string|null $currency
     *
     * @return string
     */
    public function asPrice(int $price, string $currency = null): string
    {
        return Price::AS_STRING($price, $currency);
    }
}