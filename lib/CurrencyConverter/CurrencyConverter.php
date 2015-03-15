<?php

namespace Sprain\CurrencyConverter;

use Doctrine\Common\Cache\Cache;
use Sprain\CurrencyConverter\Conversion\Conversion;
use Sprain\CurrencyConverter\Exception\InvalidCurrencyException;
use Sprain\CurrencyConverter\Exception\MissingArgumentException;
use Sprain\CurrencyConverter\Exception\UnsupportedConversionException;
use Sprain\CurrencyConverter\Provider\Abstracts\AbstractCacheableProvider;
use Sprain\CurrencyConverter\Provider\Interfaces\ProviderInterface;
use Symfony\Component\Validator\Constraints\Currency;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validation;

class CurrencyConverter
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
     * Providers
     *
     * @var array
     */
    protected $providers = array();

    /**
     * @var \Doctrine\Common\Cache\Cache
     */
    protected $cache;

    /**
     * @var \Symfony\Component\Validator\ValidatorInterface
     */
    protected $validator;

    /**
     * The provider which did the conversion
     *
     * @var ProviderInterface
     */
    protected $successfulProvider;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->validator = Validation::createValidator();
    }

    /**
     * Set the amount to be converted.
     *
     * @param  int $amount
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = (float) $amount;

        return $this;
    }

    /**
     * An alias method for setAmount to create more human-readable method chains:
     *
     * Example $converter->convert(100)->from('CHF')->to('USD')->quick()
     *
     * @param  $amount
     * @return CurrencyConverter
     */
    public function convert($amount)
    {
        return $this->setAmount($amount);
    }

    /**
     * Set the base currency
     *
     * @param  string $baseCurrency
     * @return $this
     * @throws InvalidCurrencyException
     */
    public function setBaseCurrency($baseCurrency)
    {
        $baseCurrency = strtoupper($baseCurrency);

        $violations = $this->validator->validateValue($baseCurrency, array(new Currency(), new NotBlank()));
        if ($violations->count() > 0) {

            throw new InvalidCurrencyException(sprintf('"%s" is not a valid currency.', $baseCurrency));
        }

        $this->baseCurrency = $baseCurrency;

        return $this;
    }

    /**
     * An alias method for setBaseCurrency to create more human-readable method chains:
     *
     * Example $converter->convert(100)->from('CHF')->to('USD')->quick()
     *
     * @param  $from
     * @return CurrencyConverter
     */
    public function from($from)
    {
        return $this->setBaseCurrency($from);
    }

    /**
     * Set the target currency
     *
     * @param  string $targetCurrency
     * @return $this
     * @throws InvalidCurrencyException
     */
    public function setTargetCurrency($targetCurrency)
    {
        $targetCurrency = strtoupper($targetCurrency);

        $violations = $this->validator->validateValue($targetCurrency, array(new Currency(), new NotBlank()));
        if ($violations->count() > 0) {

            throw new InvalidCurrencyException(sprintf('"%s" is not a valid currency.', $targetCurrency));
        }

        $this->targetCurrency = $targetCurrency;

        return $this;
    }

    /**
     * An alias method for setTargetCurrency to create more human-readable method chains:
     *
     * Example $converter->convert(100)->from('CHF')->to('USD')->quick()
     *
     * @param  $to
     * @return CurrencyConverter
     */
    public function to($to)
    {
        return $this->setTargetCurrency($to);
    }

    /**
     * A quick conversion, simply returns the converted value
     *
     * @return float
     * @throws MissingArgumentException
     * @throws UnsupportedConversionException
     */
    public function quick()
    {
        $this->checkBeforeConversion();

        $conversion = new Conversion();
        $conversion->setAmountInBaseCurrency($this->amount);
        $conversion->setExchangeRate($this->getExchangeRate());

        return $conversion->getAmountInTargetCurrency();
    }

    /**
     * Get the conversion object
     *
     * @return Conversion
     * @throws MissingArgumentException
     * @throws UnsupportedConversionException
     */
    public function getConversion()
    {
        $this->checkBeforeConversion();

        $conversion = new Conversion();
        $conversion->setBaseCurrency($this->baseCurrency);
        $conversion->setTargetCurrency($this->targetCurrency);
        $conversion->setAmountInBaseCurrency($this->amount);
        $conversion->setExchangeRate($this->getExchangeRate());
        $conversion->setProvider($this->successfulProvider);

        return $conversion;
    }

    /**
     * Get the exchange rate from providers
     *
     * @return float
     * @throws UnsupportedConversionException
     */
    protected function getExchangeRate()
    {
        foreach($this->getProviders() as $provider){
            try{
                $exchangeRate = $provider
                    ->setBaseCurrency($this->baseCurrency)
                    ->setTargetCurrency($this->targetCurrency)
                    ->getExchangeRate();

                $this->successfulProvider = $provider;

                return $exchangeRate;

            } catch(\Exception $e){}
        }

        throw new UnsupportedConversionException($this->baseCurrency, $this->targetCurrency);
    }

    /**
     * Add a provider
     *
     * @param ProviderInterface $provider
     * @param int $priority Higher values have higher priority
     */
    public function addProvider(ProviderInterface $provider, $priority = 0)
    {
        // Add cache, if possible
        if (null !== $this->cache && $provider instanceof AbstractCacheableProvider) {
            $provider->setCache($this->cache);
        }

        $this->providers[] = array(
            'provider' => $provider,
            'priority' => $priority
        );
    }

    /**
     * Get providers ordered by priority
     *
     * @return array
     */
    public function getProviders()
    {
        usort($this->providers, function ($a, $b) {

            if ($a['priority'] == $b['priority']) {
                return 0;
            }

            return $a['priority'] < $b['priority'] ? 1 : -1;
        });

        $providers = array();
        foreach($this->providers as $provider) {
            $providers[] = $provider['provider'];
        }

        return $providers;
    }

    /**
     * Set a cache
     *
     * @param Cache $cache
     */
    public function setCache(Cache $cache = null)
    {
        $this->cache = $cache;
    }

    /**
     * Checks if all mandatory arguments have been provided
     *
     * @throws MissingArgumentException
     */
    protected function checkBeforeConversion()
    {
        if (null == $this->baseCurrency) {

            throw new MissingArgumentException('You must provide a base currency by calling "from()"');
        }

        if (null == $this->targetCurrency) {

            throw new MissingArgumentException('You must provide a target currency by calling "to()"');
        }

        if (null == $this->amount) {

            throw new MissingArgumentException('You must provide an amount by calling "convert()"');
        }
    }
}