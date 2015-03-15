<?php


use Sprain\CurrencyConverter\Provider\SwissGovernmentProvider;
use Sprain\CurrencyConverter\Tests\Provider;

class SwissGovernmentProviderTest extends PHPUnit_Framework_TestCase
{
    public function getProvider(array $browserCalls = null, $cache = null)
    {
        if ($cache) {
            $cache = new \Doctrine\Common\Cache\ArrayCache();
        }

        return new SwissGovernmentProvider($this->getBrowserMock($browserCalls), $cache);
    }

    public function testGetSupportedCurrencies()
    {
        $this->assertEquals(array('CHF'), $this->getProvider()->getSupportedCurrencies());
    }

    public function testGetExchangeRate()
    {
        $browserCalls = array(
            'get' => array(
                'calls' => $this->exactly(2),
            ),
            'response' => array(
                'getStatusCode' => array(
                    'calls' => $this->exactly(2),
                ),
                'getContent' => array(
                    'calls' => $this->exactly(2),
                )
            )
        );

        $provider = $this->getProvider($browserCalls);
        $provider->setBaseCurrency('CHF');
        $provider->setTargetCurrency('USD');

        $this->assertSame(0.986, round($provider->getExchangeRate(), 3));
        $this->assertSame(0.986, round($provider->getExchangeRate(), 3));
    }

    public function testGetExchangeRateWithCache()
    {
        $browserCalls = array(
            'get' => array(
                'calls' => $this->once(),
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

        $provider = $this->getProvider($browserCalls, true);
        $provider->setBaseCurrency('CHF');
        $provider->setTargetCurrency('USD');

        // only one call to the browser, no matter how many exchange rates we ask for
        $this->assertSame(0.986, round($provider->getExchangeRate(), 3));
        $this->assertSame(0.986, round($provider->getExchangeRate(), 3));

        $provider->setBaseCurrency('EUR');
        $provider->setTargetCurrency('CHF');

        $this->assertSame(1.077, round($provider->getExchangeRate(), 3));
    }

    /**
     * @expectedException Sprain\CurrencyConverter\Provider\Exception\ProviderUnavailableException
     */
    public function testHttpErrorResponse()
    {
        $browserCalls = array(
            'get' => array(
                'calls' => $this->once(),
            ),
            'response' => array(
                'getStatusCode' => array(
                    'calls' => $this->once(),
                    'returnValue' => 500
                )
            )
        );

        $provider = $this->getProvider($browserCalls);
        $provider->setBaseCurrency('CHF');
        $provider->setTargetCurrency('USD');

        $provider->getExchangeRate();
    }

    /**
     * @expectedException \Exception
     * @dataProvider badApiContentProvider
     */
    public function testBadApiContent($content)
    {
        $browserCalls = array(
            'get' => array(
                'calls' => $this->once(),
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
        $provider->setBaseCurrency('CHF');
        $provider->setTargetCurrency('USD');

        $provider->getExchangeRate();
    }

    public function badApiContentProvider()
    {
        return array(
            array(''),
            array('foobar'),
        );
    }

    /**
     * Make sure an exception is thrown if the api returns no valid result
     *
     * @expectedException Sprain\CurrencyConverter\Provider\Exception\UnsupportedConversionException
     * @dataProvider unsupportedCurrencyProvider
     */
    public function testUnsupportedCurrencies($baseCurrency, $targetCurrency)
    {
        $browserCalls = array(
            'get' => array(
                'calls' => $this->once(),
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
        $provider->setBaseCurrency($baseCurrency);
        $provider->setTargetCurrency($targetCurrency);

        print $provider->getExchangeRate();
    }

    public function unsupportedCurrencyProvider()
    {
        return array(
            array('FOO', 'CHF'),
            array('CHF', 'FOO'),
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

        $browser->expects($calls)
            ->method('get')
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
        $returnValue = file_get_contents(__DIR__ . '/../../testfiles/SwissGovernmentProviderResponse.xml');

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