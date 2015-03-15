<?php

require_once(__DIR__ . '/../../../testfiles/Provider/TestProvider.php');

/**
 * Test common functionalities
 */
class AbstractProviderTest extends PHPUnit_Framework_TestCase
{
    public function getProvider()
    {
        return new TestProvider();
    }

    public function testGetExchangeRate()
    {
        $provider = $this->getProvider();
        $provider->setBaseCurrency('USD');
        $provider->setTargetCurrency('EUR');

        $this->assertSame(1.20, $provider->getExchangeRate());
    }

    /**
     * @expectedException Sprain\CurrencyConverter\Provider\Exception\UnsupportedConversionException
     * @dataProvider unsupportedCurrencyProvider
     */
    public function testUnsupportedCurrencies($baseCurrency, $targetCurrency)
    {
        $provider = $this->getProvider();
        $provider->setBaseCurrency($baseCurrency);
        $provider->setTargetCurrency($targetCurrency);

        $provider->getExchangeRate();
    }

    public function unsupportedCurrencyProvider()
    {
        return array(
            array('FOO', 'BAR'),
            array('FOO', 'CHF'),
            array('CHF', 'BAR')
        );
    }

    /**
     * @expectedException Sprain\CurrencyConverter\Provider\Exception\MissingArgumentException
     * @dataProvider incompleteDataProvider
     */
    public function testGettingExchangeRateWithIncompleteData($setterMethod, $setterValue)
    {
        $provider = $this->getProvider();
        if (null !== $setterMethod) {
            $provider->$setterMethod($setterValue);
        }
        $provider->getExchangeRate();
    }

    public function incompleteDataProvider()
    {
        return array(
            array(null, null),
            array('setBaseCurrency', 'USD'),
            array('setTargetCurrency', 'USD')
        );
    }
}