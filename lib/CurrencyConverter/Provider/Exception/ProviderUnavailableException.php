<?php

namespace Sprain\CurrencyConverter\Provider\Exception;

class ProviderUnavailableException extends \Exception
{
    public function __construct($providerName)
    {
        parent::__construct(sprintf('The "%s" provider is currently not available. This might be a temporary problem.', $providerName));
    }
}