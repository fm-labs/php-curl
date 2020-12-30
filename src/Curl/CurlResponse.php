<?php
declare(strict_types=1);

namespace FmLabs\Curl\Exception;

class CurlResponse
{
    /**
     * @var array Curl Info
     */
    private $info;

    /**
     * @var string Raw response body
     */
    private $result;

    /**
     * @var string Raw response header
     */
    private $header;

    /**
     * @var array Parsed header
     */
    private $headerData = [];

    /**
     * @var string Parsed result
     */
    private $body;
    /**
     * @var array
     */
    private $request;

    /**
     * CurlResponse constructor.
     *
     * @param array $request Curl request options
     * @param string $result Curl raw result
     * @param string $header Curl raw headers
     * @param array $info Curl connection info
     */
    public function __construct(array $request, string $result, string $header, array $info)
    {
        // request options
        $this->request = $request;

        // response
        $this->result = $result;
        $this->header = $header;
        $this->info = $info;

        // parsed header
        $this->headerData = [];
        $this->parseHeader();

        // parsed body
        $this->body = '';
        $this->parseBody();
    }

    /**
     * @return void
     */
    protected function parseHeader(): void
    {
        $lines = explode("\n", $this->header);
        foreach ($lines as $line) {
            if (preg_match("@^([\w\-]+):(.*)$@", $line, $match)) {
                $this->headerData[strtolower($match[1])] = $match[2];
            } elseif (preg_match("/(.+) ([0-9]{3}) (.+)\r\n/DU", $line, $match)) {
                $this->headerData['http_version'] = $match[1];
                $this->headerData['http_code'] = $match[2];
                $this->headerData['http_reason_phrase'] = $match[3];
            } elseif (preg_match("/^[\r\s]*$/", $line)) {
                continue;
            } else {
                $this->headerData[] = $line;
                //throw new \Exception("Could not parse header: '$line'");
            }
        }
    }

    /**
     * @return void
     */
    protected function parseBody(): void
    {
        //@TODO Parse body
        $this->body = $this->result;
    }

    /**
     * @param string|null $key Header key. Returns all header data if key is NULL.
     * @return array|mixed|null
     */
    public function getHeader(?string $key = null)
    {
        if ($key === null) {
            return $this->headerData;
        }

        return $this->headerData[$key] ?? null;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @param string|null $key Header key. Returns all header data if key is NULL.
     * @return array|mixed|null
     */
    public function getInfo(?string $key = null)
    {
        if ($key === null) {
            return $this->info;
        }

        return $this->info[$key] ?? null;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return (int)$this->getInfo('http_code');
    }

    /**
     * @return string
     */
    public function getRawHeader(): string
    {
        return $this->header;
    }

    /**
     * @return string
     */
    public function getRawResult(): string
    {
        return $this->result;
    }
}
