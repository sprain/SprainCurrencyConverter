<?php

require_once(__DIR__ . '/../testfiles/Provider/TestProvider.php');
require_once(__DIR__ . '/../testfiles/Provider/TestCacheableProvider.php');

use Sprain\CurrencyConverter\CurrencyConverter;
use Sprain\CurrencyConverter\Tests\Provider;

class CurrencyConverterTest extends PHPUnit_Framework_TestCase
{
    public function getCurrencyConverter()
    {
        return new CurrencyConverter();
    }

    /**
     * @dataProvider currencyProvider
     */
    public function testConversion($from, $to)
    {
        $converter = $this->getCurrencyConverter();
        $converter->addProvider(new TestProvider());

        $result = $converter->setAmount(100)->setBaseCurrency($from)->setTargetCurrency($to)->getConversion();
        $this->assertInstanceOf('Sprain\CurrencyConverter\Conversion\Conversion', $result);

        $this->assertSame(strtoupper($from), $result->getBaseCurrency());
        $this->assertSame(strtoupper($to),   $result->getTargetCurrency());
        $this->assertSame(100.00,            $result->getAmountInBaseCurrency());
        $this->assertSame(120.00,            $result->getAmountInTargetCurrency());
        $this->assertSame(1.20,              $result->getExchangeRate());
        $this->assertSame('TestProvider',    $result->getProvider()->getName());
    }

    /**
     * @dataProvider currencyProvider
     */
    public function testQuickConversion($from, $to)
    {
        $converter = $this->getCurrencyConverter();
        $converter->addProvider(new TestProvider());

        $result = $converter->setAmount('100')->setBaseCurrency($from)->setTargetCurrency($to)->quick();
        $this->assertSame(120.00, $result);
    }

    /**
     * @dataProvider currencyProvider
     */
    public function testConversionWithAlias($from, $to)
    {
        $converter = $this->getCurrencyConverter();
        $converter->addProvider(new TestProvider());

        $result = $converter->convert('100')->from($from)->to($to)->quick();
        $this->assertSame(120.00, $result);
    }

    public function currencyProvider()
    {
        return array(
            array('USD', 'EUR'),
            array('USD', 'eur'),
            array('usd', 'eur'),
            array('EUR', 'usd'),
            array('eur', 'USD'),
        );
    }

    /**
     * @dataProvider orderProvider
     */
    public function testProviderOrdering($order1, $order2, $successfulProviderName)
    {
        $converter = $this->getCurrencyConverter();
        $converter->addProvider(new TestProvider(), $order1);
        $converter->addProvider(new TestCacheableProvider($this->getExchangeRateGetterMock()), $order2);

        $result = $converter->setAmount(100)->setBaseCurrency('USD')->setTargetCurrency('EUR')->getConversion();

        $this->assertSame($successfulProviderName, $result->getProvider()->getName());
    }

    public function orderProvider()
    {
        return array(
            array(-1, 2, 'TestCacheableProvider'),
            array(0,  2, 'TestCacheableProvider'),
            array(1,  2, 'TestCacheableProvider'),

            array(1,  null,  'TestProvider'),
            array(null,  1,  'TestCacheableProvider'),
            array(1,     1,  'TestCacheableProvider'),

            array(2, -1, 'TestProvider'),
            array(2,  0, 'TestProvider'),
            array(2,  1, 'TestProvider'),
        );
    }

    public function testSetCacheToCacheableProvider()
    {
        $cacheableProvider = new TestCacheableProvider($this->getExchangeRateGetterMock());

        $converter = $this->getCurrencyConverter();
        $converter->setCache(new \Doctrine\Common\Cache\ArrayCache());
        $converter->addProvider($cacheableProvider);

        $this->assertInstanceOf('\Doctrine\Common\Cache\ArrayCache', $cacheableProvider->getCache());
    }

    public function testSetNoCacheToCacheableProvider()
    {
        $cacheableProvider = new TestCacheableProvider($this->getExchangeRateGetterMock());

        $converter = $this->getCurrencyConverter();
        $converter->addProvider($cacheableProvider);

        $this->assertNull($cacheableProvider->getCache());
    }

    /**
     * @expectedException Sprain\CurrencyConverter\Exception\MissingArgumentException
     * @dataProvider skipSetterProvider
     */
    public function testMissingArguments($skip)
    {
        $converter = $this->getCurrencyConverter();

        if (!in_array(1, $skip)){ $converter->from('USD'); }
        if (!in_array(2, $skip)){ $converter->to('EUR'); }
        if (!in_array(3, $skip)){ $converter->convert(100); }

        $converter->quick();
    }

    public function skipSetterProvider()
    {
        return array(
            array(array(1,2,3)),
            array(array(1,2)),
            array(array(1,3)),
            array(array(2,3)),
            array(array(1)),
            array(array(2)),
            array(array(3))
        );
    }

    /**
     * @expectedException Sprain\CurrencyConverter\Exception\InvalidCurrencyException
     * @dataProvider invalidCurrencyProvider
     */
    public function testInvalidBaseCurrency($invalidCurrency)
    {
        $converter = $this->getCurrencyConverter();
        $converter->setBaseCurrency($invalidCurrency);

    }

    /**
     * @expectedException Sprain\CurrencyConverter\Exception\InvalidCurrencyException
     * @dataProvider invalidCurrencyProvider
     */
    public function testInvalidTargetCurrency($invalidCurrency)
    {
        $converter = $this->getCurrencyConverter();
        $converter->setTargetCurrency($invalidCurrency);

    }

    public function invalidCurrencyProvider()
    {
        return array(
            array(null),
            array(''),
            array('foo'),
            array('FOO'),
        );
    }

    public function getExchangeRateGetterMock()
    {
        $exchangeRateGetter = $this->getMockBuilder('stdClass')
            ->disableOriginalConstructor()
            ->setMethods(array('get'))
            ->getMock();

        $exchangeRateGetter->expects($this->any())
            ->method('get')
            ->will($this->returnValue(1.20));

        return $exchangeRateGetter;
    }
}