<?php

namespace Sprain\CurrencyConverter\Exception;

class UnsupportedConversionException extends \Exception
{
    public function __construct($baseCurrency, $targetCurrency)
    {
        $message = sprintf('No provider has been able to convert "%s" to "%s"', $baseCurrency, $targetCurrency);

        parent::__construct($message);
    }
}