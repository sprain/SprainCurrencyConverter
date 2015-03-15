<?php

namespace Sprain\CurrencyConverter\Conversion;

use Sprain\CurrencyConverter\Provider\Interfaces\ProviderInterface;

class Conversion
{
    protected $baseCurrency;

    protected $targetCurrency;

    protected $amountInBaseCurrency;

    protected $exchangeRate;

    protected $provider;

    public function setBaseCurrency($baseCurrency)
    {
        $this->baseCurrency = $baseCurrency;
    }

    public function getBaseCurrency()
    {
        return $this->baseCurrency;
    }

    public function setTargetCurrency($targetCurrency)
    {
        $this->targetCurrency = $targetCurrency;
    }

    public function getTargetCurrency()
    {
        return $this->targetCurrency;
    }

    public function setAmountInBaseCurrency($amountInBaseCurrency)
    {
        $this->amountInBaseCurrency = $amountInBaseCurrency;
    }

    public function getAmountInBaseCurrency()
    {
        return $this->amountInBaseCurrency;
    }

    public function getAmountInTargetCurrency()
    {
        return $this->amountInBaseCurrency * $this->exchangeRate;
    }

    public function setExchangeRate($exchangeRate)
    {
        $this->exchangeRate = $exchangeRate;
    }

    public function getExchangeRate()
    {
        return $this->exchangeRate;
    }

    public function setProvider(ProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    public function getProvider()
    {
        return $this->provider;
    }
}