<?php

namespace Sprain\CurrencyConverter\Provider;

use Buzz\Browser;
use Sprain\CurrencyConverter\Provider\Abstracts\AbstractCacheableProvider;
use Sprain\CurrencyConverter\Provider\Exception\ProviderUnavailableException;
use Sprain\CurrencyConverter\Provider\Exception\UnsupportedConversionException;

/**
 * YahooProvider
 *
 * Get exchange rates based on http://finance.yahoo.com/
 */
class YahooProvider extends AbstractCacheableProvider
{
    /**
     * Base url of the Yahoo Finance api
     *
     * @var string
     */
    protected $baseUrl = 'http://finance.yahoo.com/d/quotes.csv?e=.csv&f=sl1d1t1&s=%s%s=X';

    /**
     * Browser to load data from the api
     *
     * @var Browser
     */
    protected $browser;

    /**
     * @param Browser $browser
     */
    public function __construct(Browser $browser)
    {
        $this->browser   = $browser;
    }

    /**
     * Get exchange rate
     *
     * @return float
     * @throws UnsupportedConversionException
     */
    public function doGetExchangeRate()
    {
        $url = sprintf($this->baseUrl, $this->baseCurrency, $this->targetCurrency);
        $response = $this->browser->get($url);

        if (200 !== $response->getStatusCode()) {

            throw new ProviderUnavailableException($this->getName());
        }

        $data = explode(',', $response->getContent());

        if (!isset($data[1]) || !is_numeric($data[1])) {

            throw new UnsupportedConversionException($this->baseCurrency, $this->targetCurrency);
        }

        return $data[1];
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Yahoo';
    }
}