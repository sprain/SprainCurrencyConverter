<?php

namespace Sprain\CurrencyConverter\Provider\Interfaces;

interface ProviderInterface
{
    public function setBaseCurrency($baseCurrency);

    public function setTargetCurrency($targetCurrency);

    public function getExchangeRate();
}