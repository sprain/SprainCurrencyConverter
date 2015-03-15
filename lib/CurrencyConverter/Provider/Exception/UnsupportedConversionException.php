<?php

namespace Sprain\CurrencyConverter\Provider\Exception;

class UnsupportedConversionException extends \Exception
{
    public function __construct($baseCurrency, $targetCurrency)
    {
        $message = sprintf('This provider is unable to convert "%s" to "%s"', $baseCurrency, $targetCurrency);

        parent::__construct($message);
    }
}