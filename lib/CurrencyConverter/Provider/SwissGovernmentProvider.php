<?php

namespace Sprain\CurrencyConverter\Provider;

use Buzz\Browser;
use Doctrine\Common\Cache\Cache;
use Sprain\CurrencyConverter\Provider\Abstracts\AbstractProvider;
use Sprain\CurrencyConverter\Provider\Exception\ProviderUnavailableException;
use Sprain\CurrencyConverter\Provider\Exception\UnsupportedConversionException;

/**
 * SwissGovernmentProvider
 *
 * Get the official exchange rates used by Swiss government offices
 * Only supports Swiss Francs (CHF).
 *
 * See http://www.estv.admin.ch/mwst/dienstleistungen/00304/index.html?lang=de
 */
class SwissGovernmentProvider extends AbstractProvider
{
    /**
     * Base url
     *
     * @var string
     */
    protected $baseUrl = 'http://www.afd.admin.ch/publicdb/newdb/mwst_kurse/wechselkurse.php';

    /**
     * Cache to save responses from the api
     *
     * @var Cache
     */
    protected $cache;


    /**
     * @param Browser $browser
     * @param Cache   $cache
     */
    public function __construct(Browser $browser, Cache $cache = null)
    {
        $this->browser = $browser;
        $this->cache   = $cache;
    }

    /**
     * Get exchange rate
     *
     * @return float
     * @throws UnsupportedConversionException
     */
    public function doGetExchangeRate()
    {
        $content = $this->getApiContent();

        // \Exception might be thrown here as try/catch seems not to work with SimpleXMLElement
        // see http://stackoverflow.com/a/4147354/407697
        $xml = new \SimpleXMLElement($content);

        // Cache the content now we know it is valid xml
        if (null !== $this->cache) {
            $this->cache->save('SwissGovernmentProviderXML', $content, 24 * 60 * 60);
        }

        if ('CHF' == $this->targetCurrency) {
            $currencyToLookFor = $this->baseCurrency;
        } else {
            $currencyToLookFor = $this->targetCurrency;
        }

        foreach($xml->devise as $devise){
            if($devise['code'] == strtolower($currencyToLookFor)){

                $units = str_replace(' '.$currencyToLookFor, '', $devise->waehrung);
                $exchangeRate = (float) $devise->kurs / $units;

                if ('CHF' == $this->baseCurrency) {
                    $exchangeRate = (float)  1 / $exchangeRate;
                }

                return $exchangeRate;
            }
        }

        throw new UnsupportedConversionException($this->baseCurrency, $this->targetCurrency);
    }

    /**
     * @inheritdoc
     */
    public function getSupportedCurrencies()
    {
        return array('CHF');
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'admin.ch';
    }

    protected function getApiContent()
    {
        if (null !== $this->cache) {
            if ($content = $this->cache->fetch('SwissGovernmentProviderXML')) {

                return $content;
            }
        }

        $url = $this->baseUrl;
        $response = $this->browser->get($url);

        if (200 !== $response->getStatusCode()) {

            throw new ProviderUnavailableException();
        }

        return  $response->getContent();
    }
}