# SprainCurrencyConverter

**SprainCurrencyConverter** is a php library to convert currencies. It allows great flexibility by use of built-in or
custom providers.

[![Build Status](https://travis-ci.org/sprain/CurrencyConverter.png)](https://travis-ci.org/sprain/CurrencyConverter)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sprain/CurrencyConverter/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/sprain/CurrencyConverter/?branch=master)

## Installation

Add SprainCurrencyConverter to your composer.json:

```js
{
    "require": {
        "sprain/currency-converter": "~0.1"
    }
}
```

Now tell composer to download the bundle by running the command:

``` bash
$ php composer.phar update sprain/currency-converter
```

## Usage

```php
<?php

include 'vendor/autoload.php';

$converter = new \Sprain\CurrencyConverter\CurrencyConverter();

// Optionally, add a cache which will be used by some of the providers
$cache = new \Doctrine\Common\Cache\FilesystemCache(sys_get_temp_dir());
$converter->setCache($cache);

// Add at least one provider with its dependencies
$browser = new Buzz\Browser();
$converter->addProvider(new \Sprain\CurrencyConverter\Provider\FixerIoProvider($browser));

// Do a quick conversion
$convertedAmount = $converter->convert(100)->from('USD')->to('EUR')->quick();

// Or alternatively, get a conversion object with more data
$conversion = $converter->convert(100)->from('CHF')->to('USD')->getConversion();
$convertedAmount    = $conversion->getAmountInTargetCurrency();
$exchangeRate       = $conversion->getExchangeRate();
$successfulProvider = $conversion->getProvider();
```

For more advanced usage like caching, adding more providers and creating your own providers, read the docs.


## License
This bundle is under the MIT license. See the complete license in the bundle:

    Resources/meta/LICENSE