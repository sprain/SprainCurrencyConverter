<?php

class TestProvider extends \Sprain\CurrencyConverter\Provider\Abstracts\AbstractProvider
{
    public function doGetExchangeRate()
    {
        return 1.20;
    }

    public function getSupportedCurrencies()
    {
        return array('USD', 'EUR');
    }

    public function getName()
    {
        return 'TestProvider';
    }
}