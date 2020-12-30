<?php
declare(strict_types=1);

namespace FmLabs\Curl\Exception;

class CurlTransportException extends CurlException
{
    /**
     * @var array Curl info
     */
    private $info;

    /**
     * CurlError constructor.
     *
     * @param string $error Curl error
     * @param int $errno Curl errno
     * @param array $info Curl info
     */
    public function __construct(string $error, int $errno, array $info = [])
    {
        parent::__construct($error ?: curl_strerror($errno), $errno, null);
        $this->info = $info;
    }

    /**
     * @return array
     */
    public function getInfo(): array
    {
        return $this->info;
    }
}
