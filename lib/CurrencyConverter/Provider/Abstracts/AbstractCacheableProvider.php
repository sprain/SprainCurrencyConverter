<?php

namespace Sprain\CurrencyConverter\Provider\Abstracts;

use Doctrine\Common\Cache\Cache;
use Sprain\CurrencyConverter\Provider\Exception\MissingArgumentException;
use Sprain\CurrencyConverter\Provider\Exception\UnsupportedConversionException;
use Sprain\CurrencyConverter\Provider\Interfaces\ProviderInterface;

abstract class AbstractCacheableProvider extends AbstractProvider
{
    /**
     * @var Cache
     */
    protected $cache;

    /**
     * Get the exchange rate in providers which support caching
     *
     * @return float
     * @throws MissingArgumentException
     */
    public function getExchangeRate()
    {
        if (null !== $this->cache) {
            if ($exchangeRate = $this->cache->fetch($this->getCacheKey())) {

                return $exchangeRate;
            }
        }

        $exchangeRate = parent::getExchangeRate();

        if (null !== $this->cache) {
            $this->cache->save($this->getCacheKey(), $exchangeRate, 24 * 60 * 60);
        }

        return $exchangeRate;
    }

    /**
     * Set the cache
     *
     * @param Cache $cache
     */
    public function setCache(Cache $cache)
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * Get the cache
     *
     * @return Cache
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Get a unique key for caching purposes
     *
     * @return string
     */
    public function getCacheKey()
    {
        return md5('SprainCurrencyConverter_' . $this->getName() . $this->baseCurrency . $this->targetCurrency);
    }
}