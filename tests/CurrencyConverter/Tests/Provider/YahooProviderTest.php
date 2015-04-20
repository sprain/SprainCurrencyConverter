<?php

use Sprain\CurrencyConverter\Tests\Provider;
use \Sprain\CurrencyConverter\Provider\YahooProvider;

class YahooProviderTest extends PHPUnit_Framework_TestCase
{
    public function getProvider(array $browserCalls = null)
    {
        return new YahooProvider($this->getBrowserMock($browserCalls));
    }

    public function testGetSupportedCurrencies()
    {
        $this->assertEquals(array(), $this->getProvider()->getSupportedCurrencies());
    }

    public function testGetExchangeRate()
    {
        $browserCalls = array(
            'get' => array(
                'calls' => $this->once(),
                'with'  => 'http://finance.yahoo.com/d/quotes.csv?e=.csv&f=sl1d1t1&s=USDEUR=X'
            ),
            'response' => array(
                'getStatusCode' => array(
                    'calls' => $this->once(),
                ),
                'getContent' => array(
                    'calls' => $this->once(),
                )
            )
        );

        $provider = $this->getProvider($browserCalls);
        $provider->setBaseCurrency('USD');
        $provider->setTargetCurrency('EUR');

        $this->assertEquals('0.9459', $provider->getExchangeRate());
    }

    /**
     * @expectedException Sprain\CurrencyConverter\Provider\Exception\ProviderUnavailableException
     */
    public function testHttpErrorResponse()
    {
        $browserCalls = array(
            'get' => array(
                'calls' => $this->once(),
                'with'  => 'http://finance.yahoo.com/d/quotes.csv?e=.csv&f=sl1d1t1&s=USDEUR=X'
            ),
            'response' => array(
                'getStatusCode' => array(
                    'calls' => $this->once(),
                    'returnValue' => 500
                )
            )
        );

        $provider = $this->getProvider($browserCalls);
        $provider->setBaseCurrency('USD');
        $provider->setTargetCurrency('EUR');

        $provider->getExchangeRate();
    }

    /**
     * @expectedException Sprain\CurrencyConverter\Provider\Exception\UnsupportedConversionException
     * @dataProvider badApiContentProvider
     */
    public function testBadApiContent($content)
    {
        $browserCalls = array(
            'get' => array(
                'calls' => $this->once(),
                'with'  => 'http://finance.yahoo.com/d/quotes.csv?e=.csv&f=sl1d1t1&s=USDEUR=X'
            ),
            'response' => array(
                'getStatusCode' => array(
                    'calls' => $this->once(),
                ),
                'getContent' => array(
                    'calls' => $this->once(),
                    'returnValue'  => $content
                )
            )
        );

        $provider = $this->getProvider($browserCalls);
        $provider->setBaseCurrency('USD');
        $provider->setTargetCurrency('EUR');

        $provider->getExchangeRate();
    }

    public function badApiContentProvider()
    {
        return array(
            array(''),
            array('foobar'),
            array('{"some":"random","json":"content"}'),
            array('"USDEUR=X"0.9459') #no commas in csv
        );
    }

    public function getBrowserMock(array $browserCalls = null)
    {
        $browser = $this->getMockBuilder('Buzz\Browser')
            ->disableOriginalConstructor()
            ->setMethods(array('get'))
            ->getMock();

        $calls = $this->never();

        if (isset($browserCalls['get']['calls'])) {
            $calls = $browserCalls['get']['calls'];
        }

        $with = null;
        if (isset($browserCalls['get']['with'])) {
            $with = $browserCalls['get']['with'];
        }

        $browser->expects($calls)
            ->method('get')
            ->with($with)
            ->will($this->returnValue($this->getResponseMock($browserCalls)));

        return $browser;
    }

    public function getResponseMock($browserCalls)
    {
        $response = $this->getMockBuilder('Buzz\Message\Response')
            ->disableOriginalConstructor()
            ->setMethods(array('getStatusCode', 'getContent'))
            ->getMock();


        $calls = $this->never();
        $returnValue = 200;

        if (isset($browserCalls['response']['getStatusCode']['calls'])) {
            $calls = $browserCalls['response']['getStatusCode']['calls'];
        }

        if (isset($browserCalls['response']['getStatusCode']['returnValue'])) {
            $returnValue = $browserCalls['response']['getStatusCode']['returnValue'];
        }

        $response->expects($calls)
            ->method('getStatusCode')
            ->will($this->returnValue($returnValue));


        $calls =  $this->never();
        $returnValue = '"USDEUR=X",0.9459,"4/20/2015","7:57am"';

        if (isset($browserCalls['response']['getContent']['calls'])) {
            $calls = $browserCalls['response']['getContent']['calls'];
        }

        if (isset($browserCalls['response']['getContent']['returnValue'])) {
            $returnValue = $browserCalls['response']['getContent']['returnValue'];
        }

        $response->expects($calls)
            ->method('getContent')
            ->will($this->returnValue($returnValue));

        return $response;
    }
}