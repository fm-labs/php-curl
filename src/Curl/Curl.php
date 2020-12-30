<?php
declare(strict_types=1);

namespace FmLabs\Curl\Exception;

/**
 * Class Curl
 *
 * @package FmLabs\Curl\Exception
 */
class Curl
{
    /**
     * @var string Curl Error
     */
    private $error;

    /**
     * @var int Curl Error No
     */
    private $errno;

    /**
     * @var array Curl info array
     */
    private $info;

    /**
     * @var resource|\CurlHandle Curl Handle
     */
    private $handle;

    /**
     * @var string Raw header string
     */
    private $headerRaw;

    /**
     * @var string Raw Response string
     */
    private $responseRaw;

    /**
     * @var \FmLabs\Curl\Exception\CurlResponse Curl response object
     */
    private $response;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var array Default curl options
     */
    private $defaults = [
        //CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.4) Gecko/20030624 Netscape/7.1 (ax)',
        CURLOPT_HTTPGET => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => 5,
    ];

    /**
     * @var array Applied curl options
     */
    private $options = [];

    /**
     * Constructor
     *
     * @param array $defaults Default curl options for every request.
     * @throws \FmLabs\Curl\Exception\CurlException
     */
    public function __construct(array $defaults = [])
    {
        $this->defaults = array_merge($this->defaults, $defaults);
    }

    /**
     * Close curl handle on object destruction.
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Initialize a new Curl Handle
     *
     * @param string|null $url Request URL
     * @param array $options Curl options
     * @return $this
     * @throws \FmLabs\Curl\Exception\CurlException
     */
    public function init(?string $url = null, array $options = [])
    {
        if ($this->handle) {
            throw new CurlException('Already initialized');
        }

        $this->options = [];
        $this->handle = curl_init();
        if ($url) {
            $this->setUrl($url);
        }

        $this->setOpts($this->defaults);
        $this->setOpts($options);

        $this->setOpt(CURLOPT_HEADERFUNCTION, [$this, 'headerCallback']);
        //$this->setOpt(CURLOPT_WRITEFUNCTION, [$this, 'writeCallback']);
        //$this->setOpt(CURLOPT_READFUNCTION, [$this, 'readCallback']);
        //$this->setOpt(CURLOPT_PASSWDFUNCTION, [$this, 'passwdCallback']);
        //$this->setOpt(CURLOPT_PROGRESSFUNCTION, [$this, 'progressCallback']);

        return $this;
    }

    /**
     * Magic setter method for options
     *
     * @param string $opt Curl option name without CURLOPT_ prefix
     * @param mixed $val Curl option value
     * @return void
     * @throws \FmLabs\Curl\Exception\CurlException
     */
    public function __set(string $opt, $val): void
    {
        $this->setOptByName($opt, $val);
    }

    /**
     * Set CURL option.
     * Wrapper for curl_setopt.
     *
     * @param int $opt CURL option or options-array
     * @param mixed|null $val CURL option value
     * @return $this
     * @throws \FmLabs\Curl\Exception\CurlException
     */
    public function setOpt(int $opt, $val = null)
    {
        if (!$this->handle) {
            throw new CurlException('Curl handle not initialized');
        }

        if (!is_long($opt)) {
            throw new CurlOptException('Invalid key type: expects a long-value', $opt);
        }

        if (!curl_setopt($this->handle, $opt, $val)) {
            throw new CurlOptException('setopt failed', $opt);
        }

        $this->options[$opt] = $val;

        return $this;
    }

    /**
     * @param string $opt Curl option name
     * @param mixed $val Curl option value
     * @return $this
     */
    public function setOptByName(string $opt, $val)
    {
        $opt = strtoupper($opt);
        if (!preg_match('/^CURLOPT/', $opt)) {
            $const = 'CURLOPT_' . $opt;
        }
        if (!defined($const)) {
            throw new CurlOptException(sprintf('%s is not a valid Curl option', $const));
        }

        return $this->setOpt(constant($const), $val);
    }

    /**
     * Set multiple curl options.
     *
     * @param array $opts Curl options key-value pairs
     * @return $this
     */
    public function setOpts(array $opts)
    {
        foreach ($opts as $_key => $_val) {
            $this->setOpt($_key, $_val);
        }

        return $this;
    }

    /**
     * Set the Request Url
     *
     * @param string $url Request URL
     * @return $this
     * @throws \FmLabs\Curl\Exception\CurlException
     */
    public function setUrl(string $url)
    {
        $this->setOpt(CURLOPT_URL, $url);

        return $this;
    }

    /**
     * Set the Request method
     *
     * @param string $method GET/POST
     * @return $this
     * @throws \FmLabs\Curl\Exception\CurlException
     */
    public function setMethod($method = 'GET')
    {
        $method = strtoupper($method);
        $opts = [];
        switch ($method) :
            case 'GET':
                $opts = [ CURLOPT_HTTPGET => true ];
                break;
            case 'POST':
                $opts = [ CURLOPT_POST => true ];
                break;
            case 'HEAD':
                $opt = [
                    CURLOPT_HTTPGET => true,
                    CURLOPT_NOBODY => true,
                    CURLOPT_HEADER => true,
                    CURLOPT_CUSTOMREQUEST => 'HEAD',
                ];
                break;
            default:
                $opts = [
                    CURLOPT_HTTPGET => true,
                    CURLOPT_CUSTOMREQUEST => $method,
                ];
                break;
        endswitch;

        return $this->setOpts($opts);
    }

    /**
     * @param array $headers Key-value pairs of header lines e.g. ['Content-Type' => 'text/html']
     * @return $this
     * @throws \FmLabs\Curl\Exception\CurlException
     */
    public function setHeaders(array $headers)
    {
        $httpHeaders = [];
        foreach ($headers as $key => $val) {
            $httpHeaders[] = sprintf('%s: %s', $key, $val);
        }

        return $this->setOpt(CURLOPT_HTTPHEADER, $httpHeaders);
    }

    /**
     * Set PostData
     *
     * @param array|string $data urlencoded string or array
     * @return $this
     * @throws \FmLabs\Curl\Exception\CurlException
     */
    public function setPostData($data)
    {
        // @TODO Check if manually urlencoding the array is necessary.
        if (is_array($data)) {
            $data = self::urlencodeArray($data);
            #$data = http_build_query($data);
        }
        #$data = urlencode($data);
        #$data = utf8_encode($data);
        #print_r($data);
        //$this->setMethod('POST');

        return $this->setOpt(CURLOPT_POSTFIELDS, $data);
    }

    /**
     * @param string|bool $cookieFile Path to cookie file. If, TRUE a temporary cookie file will be generated.
     *      The cookieFile will be used as CURLOPT_COOKIEFILE and CURLOPT_COOKIEJAR.
     * @return $this
     * @throws \FmLabs\Curl\Exception\CurlException
     */
    public function setCookie($cookieFile)
    {
        if ($cookieFile === true) {
            $cookieFile = tempnam(sys_get_temp_dir(), 'curlcookie');
        }

        return $this->setOpts([
            CURLOPT_COOKIEFILE => $cookieFile,
            CURLOPT_COOKIEJAR => $cookieFile,
        ]);
    }

    /**
     * @param bool $enable TRUE to enable verbose output
     * @return $this
     * @throws \FmLabs\Curl\Exception\CurlException
     */
    public function setVerbose(bool $enable)
    {
        return $this->setOpt(CURLOPT_VERBOSE, true);
    }

    /**
     * @param string $username Basic auth username
     * @param string|null $password Basic auth password (optional)
     * @return $this
     * @throws \FmLabs\Curl\Exception\CurlException
     */
    public function setBasicAuth(string $username, ?string $password = null)
    {
        return $this->setOpt(CURLOPT_USERPWD, static::buildUserpwd($username, $password));
    }

    /**
     * @param string $proxyHost Proxy host
     * @param int $proxyPort Proxy port
     * @param int $proxyType Proxy type
     * @return $this
     * @throws \FmLabs\Curl\Exception\CurlException
     */
    public function setProxy(string $proxyHost, int $proxyPort, int $proxyType = CURLPROXY_HTTP)
    {
        return $this->setOpts([
            CURLOPT_PROXY => $proxyHost,
            CURLOPT_PROXYPORT => $proxyPort,
            CURLOPT_PROXYTYPE => $proxyType,
        ]);
    }

    /**
     * @param string|null $proxyUser Proxy user (optional)
     * @param string|null $proxyPass Proxy password (optional)
     * @return $this
     * @throws \FmLabs\Curl\Exception\CurlException
     */
    public function setProxyAuth(string $proxyUser, ?string $proxyPass = null)
    {
        return $this->setOpt(CURLOPT_PROXYUSERPWD, static::buildUserpwd($proxyUser, $proxyPass));
    }

    /**
     * @return $this
     * @throws \FmLabs\Curl\Exception\CurlException
     */
    public function setInsecure()
    {
        return $this->setOpts([
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
    }

    /**
     * @param int|string|null $protoVersion Http protocol version.
     *      Accepts CURL_HTTP_VERSION* constants integer values
     *      Accepts protocol version strings (e.g. '1.1', '1.0')
     * @return $this
     * @throws \FmLabs\Curl\Exception\CurlException
     */
    public function setProtocolVersion($protoVersion = CURL_HTTP_VERSION_NONE)
    {
        if (!is_int($protoVersion)) {
            $map = [
                '1.0' => CURL_HTTP_VERSION_1_0,
                '1.1' => CURL_HTTP_VERSION_1_1,
                '2.0' => CURL_HTTP_VERSION_2_0,
                '2' => CURL_HTTP_VERSION_2,
                '2.0-tls' => CURL_HTTP_VERSION_2TLS,
                '2.0-prio' => CURL_HTTP_VERSION_2_PRIOR_KNOWLEDGE,
            ];
            $protoVersion = $map[$protoVersion] ?? CURL_HTTP_VERSION_NONE;
        }

        return $this->setOpt(CURLOPT_HTTP_VERSION, $protoVersion);
    }

    /**
     * Perform CURL request
     *
     * @param callable|null $callback Optional callback. Will be called after every request.
     *  The callback receives the CurlResponse object as the only argument.
     * @return $this
     * @throws \FmLabs\Curl\Exception\CurlTransportException
     */
    public function execute(?callable $callback = null)
    {
        if (!$this->handle) {
            throw new CurlException('Execution failed: Not initialized');
        }

        $result = curl_exec($this->handle);
        $this->error = curl_error($this->handle);
        $this->errno = curl_errno($this->handle);
        $this->info = curl_getinfo($this->handle);

        if ($this->errno > 0) {
            $this->close();
            throw new CurlTransportException($this->error, $this->errno, $this->info);
        }

        $this->responseRaw = $result ? $result : '';
        $this->response = new CurlResponse(
            $this->options,
            $this->responseRaw,
            $this->headerRaw,
            $this->info
        );

        if ($callback && is_callable($callback)) {
            call_user_func($callback, $this->response);
        }

        return $this;
    }

    /**
     * Reset curl options to defaults
     *
     * @return $this
     */
    public function reset()
    {
        if ($this->handle) {
            curl_reset($this->handle);
        }

        return $this;
    }

    /**
     * Close curl Handle
     *
     * @return $this
     */
    public function close()
    {
        if ($this->handle) {
            curl_close($this->handle);
            $this->handle = null;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }

    /**
     * @return int
     */
    public function getErrNo(): int
    {
        return $this->errno;
    }

    /**
     * Return Curl Handle
     *
     * @return resource|\CurlHandle|null Curl handle
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * Curl header callback method.
     *
     * @param resource $ch Curl handle
     * @param string $headerLine Raw header line
     * @return int Length of raw header line
     */
    private function headerCallback($ch, string $headerLine): int
    {
        $this->headerRaw .= $headerLine;

        return strlen($headerLine);
    }

    /**
     * Returns response object
     *
     * @return \FmLabs\Curl\Exception\CurlResponse
     */
    public function getResponse(): CurlResponse
    {
        return $this->response;
    }

    /**
     * Log a message
     *
     * @param string $message Log message
     * @param int $level Log level
     * @return $this
     */
    public function log($message, $level = LOG_INFO)
    {
        if ($this->getLogger()) {
            $this->getLogger()->log($level, $message);
        }

        return $this;
    }

    /**
     * @param \Psr\Log\LoggerInterface|null $logger Logger object
     * @return $this
     */
    public function setLogger(?\Psr\Log\LoggerInterface $logger = null)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @return \Psr\Log\LoggerInterface|null
     */
    public function getLogger(): ?\Psr\Log\LoggerInterface
    {
        return $this->logger ?? null;
    }

    /**
     * @param array $args Input args
     * @return string Urlencoded string
     */
    public static function urlencodeArray(array $args): string
    {
        $c = 0;
        $out = '';
        foreach ($args as $name => $value) {
            if ($c++ != 0) {
                $out .= '&';
            }
            $out .= urlencode("$name") . '=';
            if (is_array($value)) {
                $out .= urlencode(serialize($value));
            } else {
                $out .= urlencode("$value");
            }
        }

        return $out;
    }

    /**
     * Build userpwd string [username][:password].
     *
     * @param string $user Username
     * @param string|null $pwd Optional password
     * @return string
     */
    public static function buildUserpwd(string $user, ?string $pwd = null): string
    {
        return $pwd ? $user . ':' . $pwd : $user;
    }
}
