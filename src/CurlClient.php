<?php
declare(strict_types=1);

namespace FmLabs\Curl;

use Http\Client\HttpClient;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class CurlClient
 *
 * @package FmLabs\Curl
 */
class CurlClient implements HttpClient
{
    /**
     * @var \FmLabs\Curl\Curl
     */
    protected $curl;

    /**
     * @return \FmLabs\Curl\Curl
     */
    protected function getCurl(): Curl
    {
        if (!$this->curl) {
            $this->curl = new Curl();
        }

        return $this->curl;
    }

    /**
     * @inheritDoc
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $curlResponse = $this->getCurl()
            ->init()
            ->setUrl((string)$request->getUri())
            ->setProtocolVersion($request->getProtocolVersion())
            ->setMethod($request->getMethod())
            ->setPostData($request->getBody()->read($request->getBody()->getSize()))
            ->setHeaders($request->getHeaders())
            ->execute(/**function(CurlResponse $response) {
                // do something with response
            }**/);

        throw new \Exception('Not implemented');

        //@TODO $curlResponse -> ResponseInterface
    }
}
