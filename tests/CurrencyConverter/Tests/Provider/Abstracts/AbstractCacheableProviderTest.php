<?php

require_once(__DIR__ . '/../../../testfiles/Provider/TestCacheableProvider.php');

/**
 * Tests common caching functionalities
 */
class AbstractCacheableProviderTest extends PHPUnit_Framework_TestCase
{
    public function getProvider($withCache = true)
    {
        $exchangeRateGetterCalls = $this->exactly(2);
        if ($withCache) {
            $exchangeRateGetterCalls = $this->once();
        }

        $provider = new TestCacheableProvider($this->getExchangeRateGetterMock($exchangeRateGetterCalls));

        if ($withCache) {
            $provider->setCache(new \Doctrine\Common\Cache\ArrayCache());
        }

        return $provider;
    }

    public function testGetExchangeRate()
    {
        $provider = $this->getProvider();
        $provider->setBaseCurrency('USD');
        $provider->setTargetCurrency('EUR');

        $this->assertSame(1.20, $provider->getExchangeRate());

        //second call should return same result, but not create a second call to the ExchangeRateGetter
        $this->assertSame(1.20, $provider->getExchangeRate());
    }


    public function testGetExchangeRateWithoutCache()
    {
        $provider = $this->getProvider(false);
        $provider->setBaseCurrency('USD');
        $provider->setTargetCurrency('EUR');

        $this->assertSame(1.20, $provider->getExchangeRate());

        //second call should return same result and call the ExchangeRateGetter a second time
        $this->assertSame(1.20, $provider->getExchangeRate());
    }

    public function getExchangeRateGetterMock($exchangeRateGetterCalls)
    {
        $exchangeRateGetter = $this->getMockBuilder('stdClass')
            ->disableOriginalConstructor()
            ->setMethods(array('get'))
            ->getMock();

        $exchangeRateGetter->expects($exchangeRateGetterCalls)
            ->method('get')
            ->will($this->returnValue(1.20));

        return $exchangeRateGetter;
    }
}