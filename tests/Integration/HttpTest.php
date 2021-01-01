<?php

namespace FmLabs\Curl\Test\Integration;

use FmLabs\Curl\Curl;
use FmLabs\Curl\CurlResponse;
use PHPUnit\Framework\TestCase;

/**
 * Class IntegrationTest
 *
 * @package FmLabs\Test\Curl\Exception
 * @group http
 */
class HttpTest extends TestCase
{
    protected $testBaseUrl = 'http://localhost:8855';

    /**
     * @param string $path Request path
     * @return string
     */
    protected function _getUrl($path = '/'): string
    {
        return $this->testBaseUrl . $path;
    }

    /**
     * @return void
     */
    public function testGetRequest(): void
    {
        $curl = new Curl();
        $response = $curl
            ->init($this->_getUrl('?test=text'))
            ->execute()
            ->getResponse();

        $this->assertInstanceOf(CurlResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('This is a test', $response->getBody());
        $this->assertEquals('text', $response->getHeader('X-Test'));
    }

    /**
     * @return void
     */
    public function testRedirect(): void
    {
        $curl = new Curl();
        $response = $curl
            ->init($this->_getUrl('?test=redirect'))
            ->setOpt(CURLOPT_FOLLOWLOCATION, false)
            ->execute()
            ->getResponse();

        $this->assertInstanceOf(CurlResponse::class, $response);
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('', $response->getBody());
        $this->assertEquals('/?test=text', $response->getHeader('Location'));
        $this->assertEquals('redirect', $response->getHeader('X-Test'));
    }

    /**
     * @return void
     */
    public function testFollowRedirect(): void
    {
        $curl = new Curl();
        $response = $curl
            ->init($this->_getUrl('?test=redirect'))
            //->setOpt(CURLOPT_FOLLOWLOCATION, true)
            ->execute()
            ->getResponse();

        $this->assertInstanceOf(CurlResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('This is a test', $response->getBody());
        $this->assertEquals('text', $response->getHeader('X-Test'));
    }

    /**
     * @return void
     */
    public function testGetJson(): void
    {
        $curl = new Curl();
        $response = $curl
            ->init($this->_getUrl('?test=json'))
            ->execute()
            ->getResponse();

        $this->assertInstanceOf(CurlResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"foo":"bar"}', $response->getBody());
        $this->assertEquals('json', $response->getHeader('X-Test'));
    }
}
