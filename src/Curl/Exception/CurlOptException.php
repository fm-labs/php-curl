<?php
declare(strict_types=1);

namespace FmLabs\Curl\Exception;

use Throwable;

class CurlOptException extends CurlException
{
    /**
     * InvalidCurlOptException constructor.
     *
     * @param string $message Exception message
     * @param int $option CURLOPT_*
     * @param \Throwable|null $previous Previous error
     */
    public function __construct($message = '', $option = 0, ?Throwable $previous = null)
    {
        $message = sprintf("Failed to set curl option '%s': %s", $option, $message);
        parent::__construct($message, $option, $previous);
    }
}
