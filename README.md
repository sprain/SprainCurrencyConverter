# SprainCurrencyConverter

**SprainCurrencyConverter** is a php library to convert currencies. It allows great flexibility by use of built-in or
custom providers.

[![Build Status](https://travis-ci.org/sprain/CurrencyConverter.png)](https://travis-ci.org/sprain/CurrencyConverter)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sprain/CurrencyConverter/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/sprain/CurrencyConverter/?branch=master)

## Installation

Add SprainCurrencyConverter to your composer.json:


	{
    	"require": {
     	   "sprain/currency-converter": "~0.1"
    	}
	}


Now tell composer to download the bundle by running the command:


	$ php composer.phar update sprain/currency-converter

## Usage

```php
<?php

include 'vendor/autoload.php';

// Create an instance
$converter = new \Sprain\CurrencyConverter\CurrencyConverter();

// Optional, but recommended: add a cache which will be used by some of the providers.
// You can use any cache system supported by Doctrine Cache.
$cache = new \Doctrine\Common\Cache\FilesystemCache(sys_get_temp_dir());
$converter->setCache($cache);

// Add at least one provider with its dependencies
$browser = new Buzz\Browser();
$converter->addProvider(new \Sprain\CurrencyConverter\Provider\FixerIoProvider($browser));

// Optional: Add more providers. The converter will then loop over the providers until
// a result is found. Use the second parameter to define the provider's priority.
// Higher values mean higher priority. The highest priority provider will be checked before all others.
$converter->addProvider(new \Sprain\CurrencyConverter\Provider\SwissGovernmentProvider($browser, 255));

// Do a quick conversion
$convertedAmount = $converter->convert(100)->from('USD')->to('EUR')->quick();

// Or alternatively, get a conversion object which contains more data
$conversion = $converter->convert(100)->from('USD')->to('EUR')->getConversion();
$convertedAmount    = $conversion->getAmountInTargetCurrency();
$exchangeRate       = $conversion->getExchangeRate();
$successfulProvider = $conversion->getProvider();
```

## Providers

### Included providers

* **FixerIoProvider**<br>Exchange rates provided by [fixer.io](http://fixer.io/), which gets its data from the European Central Bank.<br>*Supported currencies:* No restrictions defined

* **SwissGovernmentProvider**<br>Exchange rates provided by the Swiss government. See [www.estv.admin.ch](http://www.estv.admin.ch/mwst/dienstleistungen/00304/index.html).<br>*Supported currencies:* CHF



### Create your own custom provider

SprainCurrencyConverter easily allows you to add your own custom provider. This is helpful if for instance you keep exchange rates locally in a database.

It's simple:

```php
<?php

namespace Acme\Your\Project;

use Sprain\CurrencyConverter\Provider\Abstracts\AbstractProvider;

// Simply extend the AbstractProvider which comes with SprainCurrencyConverter
class MyCustomProvider extends AbstractProvider
{
    public function doGetExchangeRate()
    {
    	$baseCurrency   = $this->getBaseCurrency();
    	$targetCurrency = $this->getTargetCurrency();

    	// do your thing and return the exchange rate which represents the
    	// value of 1 unit of the base currency in units of the target currency.
    	// ...

        return $exchangeRate;
    }

	// Return your provider's name.
    public function getName()
    {
        return 'My custom provider';
    }

	// Optional:
	// Add this method and return an array of supported currencies.
	// The provider will only be used if either base currency _or_ target currency
	// is a member of this array.
    public function getSupportedCurrencies()
    {
        return array('USD', 'EUR');
    }
}
```

#### Caching in custom providers

If your provider uses a remote api or any other method which takes some time to get data, you might want to cache the exchange rate.

To achieve this, simply extend `AbstractCacheableProvider` instead of `AbstractProvider`:

```php
<?php

namespace Acme\Your\Project;

use Sprain\CurrencyConverter\Provider\Abstracts\AbstractProvider;

class MyCacheableCustomProvider extends AbstractCacheableProvider
{
	...	same, same as above
}
```

If a cache was injected into `CurrencyConverter`, every exchange rate fetched by your provider will now automatically be cached for 24 hours.


## License
This library is under the MIT license. See the complete license in:

    Resources/meta/LICENSE