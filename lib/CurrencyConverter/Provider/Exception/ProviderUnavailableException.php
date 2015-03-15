<?php

namespace Sprain\CurrencyConverter\Provider\Exception;

class ProviderUnavailableException extends \Exception
{
    public function __construct()
    {
        parent::__construct('This provider is currently not available. This might be a temporary problem.');
    }
}