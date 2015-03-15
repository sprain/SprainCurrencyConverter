<?php

namespace Sprain\CurrencyConverter\Provider;

use Buzz\Browser;
use Sprain\CurrencyConverter\Provider\Abstracts\AbstractCacheableProvider;
use Sprain\CurrencyConverter\Provider\Exception\ProviderUnavailableException;
use Sprain\CurrencyConverter\Provider\Exception\UnsupportedConversionException;

/**
 * FixerIoProvider
 *
 * Get exchange rates based on http://fixer.io/
 */
class FixerIoProvider extends AbstractCacheableProvider
{
    /**
     * Base url of the fixer.io api
     *
     * @var string
     */
    protected $baseUrl = 'http://api.fixer.io/latest';

    /**
     * Browser to load data from the url
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
        $params = array(
            'base'    => $this->baseCurrency,
            'symbols' => $this->targetCurrency
        );

        $url = $this->baseUrl . '?' . http_build_query($params);
        $response = $this->browser->get($url);

        if (200 !== $response->getStatusCode()) {

            throw new ProviderUnavailableException();
        }

        $content = json_decode($response->getContent(), true);

        if (!isset($content['rates'][$this->targetCurrency])) {

            throw new UnsupportedConversionException($this->baseCurrency, $this->targetCurrency);
        }

        return $content['rates'][$this->targetCurrency];
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'fixer.io';
    }
}