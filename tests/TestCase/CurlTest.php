<?php

namespace FmLabs\Curl\Test\TestCase;

use FmLabs\Curl\Curl;
use PHPUnit\Framework\TestCase;

class CurlTest extends TestCase
{
    /**
     * @return void
     */
    public function testStaticUrlencodeArray(): void
    {
        $input = ['a' => 'b', 'c' => 'd'];
        $expected = 'a=b&c=d';
        $this->assertEquals($expected, Curl::urlencodeArray($input));
    }

    /**
     * @return void
     */
    public function testStaticBuildUserpwd(): void
    {
        $this->assertEquals('username', Curl::buildUserpwd('username'));
        $this->assertEquals('username:s3cret', Curl::buildUserpwd('username', 's3cret'));
    }

    /**
     * @return void
     */
    public function testInit(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @return void
     */
    public function testReset(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @return void
     */
    public function testClose(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @return void
     */
    public function testSetHeaders(): void
    {
        $headers = [
            'X-Test' => 'Foo',
            'Content-Type' => 'text/html',
        ];

        $curl = new Curl();
        $curl->init('http://www.example.org');
        $curl->setHeaders($headers);

        $expected = [
            'X-Test: Foo',
            'Content-Type: text/html',
        ];
        $this->assertEquals($expected, $curl->getOpts()[CURLOPT_HTTPHEADER]);
    }

    /**
     * @return void
     */
    public function testSetOpt(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @return void
     */
    public function testSetOpts(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @return void
     */
    public function testSetOptByName(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @return void
     */
    public function testSetPostData(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @return void
     */
    public function testSetUrl(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @return void
     */
    public function testSetProxy(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @return void
     */
    public function testSetInsecure(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @return void
     */
    public function testSetVerbose(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @return void
     */
    public function testSetMethod(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @return void
     */
    public function testSetBasicAuth(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @return void
     */
    public function testSetProxyAuth(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @return void
     */
    public function testSetCookie(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @return void
     */
    public function testSetProtocolVersion(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @return void
     */
    public function testExecute(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @return void
     */
    public function testGetHandle(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @return void
     */
    public function testGetResponse(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @return void
     */
    public function testGetErrNo(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @return void
     */
    public function testGetError(): void
    {
        $this->markTestIncomplete();
    }
}
