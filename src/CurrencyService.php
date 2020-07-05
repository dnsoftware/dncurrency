<?php


namespace Dnsoftware\Dncurrency;


use Dnsoftware\Dncurrency\Models\Dncurrencies;

class CurrencyService
{

    public static function displayCurrencyPanel()
    {
        $locale = app()->getLocale();
        $currencies = Dncurrencies::getCurrencies();

        return view('dncurrency::currency_panel', compact('currencies', 'locale'));
    }

}