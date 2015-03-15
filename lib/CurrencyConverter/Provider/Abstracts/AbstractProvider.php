<?php

namespace Sprain\CurrencyConverter\Provider\Abstracts;

use Doctrine\Common\Cache\Cache;
use Sprain\CurrencyConverter\Provider\Exception\MissingArgumentException;
use Sprain\CurrencyConverter\Provider\Exception\UnsupportedConversionException;
use Sprain\CurrencyConverter\Provider\Interfaces\ProviderInterface;

abstract class AbstractProvider implements ProviderInterface
{
    /**
     * The amount to be converted
     *
     * @var int
     */
    protected $amount;

    /**
     * ISO 4127 currency code
     *
     * @var string
     */
    protected $baseCurrency;

    /**
     * ISO 4127 currency code
     *
     * @var string
     */
    protected $targetCurrency;

    /**
     * Get exchange rate
     *
     * This method must return the exchange rate representing
     * one unit of base currency in target currency.
     *
     * It will be called via getExchangeRate().
     *
     * @return float
     */
    abstract public function doGetExchangeRate();

    /**
     * Get name of this provider
     *
     * @return mixed
     */
    abstract public function getName();

    /**
     * Set the base currency
     *
     * @param  string $baseCurrency
     * @return $this
     */
    public function setBaseCurrency($baseCurrency)
    {
        $this->baseCurrency = $baseCurrency;

        return $this;
    }

    /**
     * Set the target currency
     *
     * @param  string $targetCurrency
     * @return $this
     */
    public function setTargetCurrency($targetCurrency)
    {
        $this->targetCurrency = $targetCurrency;

        return $this;
    }

    /**
     * Get the exchange rate
     *
     * @return float
     * @throws MissingArgumentException
     * @throws UnsupportedConversionException
     */
    public function getExchangeRate()
    {
        if (null == $this->baseCurrency || null == $this->targetCurrency) {

            throw new MissingArgumentException('baseCurrency and targetCurrency must be set before getting the exchange rate');
        }

        if (count($this->getSupportedCurrencies()) > 0) {
            if (!in_array($this->baseCurrency, $this->getSupportedCurrencies()) &&
                !in_array($this->targetCurrency, $this->getSupportedCurrencies())) {

                throw new UnsupportedConversionException($this->baseCurrency, $this->targetCurrency);
            }
        }

        return $this->doGetExchangeRate();
    }

    /**
     * An array of supported currencies
     *
     * The provider will be skipped if neither base currency nor target currency
     * is among the supported currencies.
     *
     * return array
     */
    public function getSupportedCurrencies()
    {
        return array();
    }
}