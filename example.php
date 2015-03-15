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