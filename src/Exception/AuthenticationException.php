<?php

namespace App\Exception;

use Exception;
use InvalidArgumentException;

/**
 * Class AuthenticationException
 */
class AuthenticationException extends InvalidArgumentException
{
    private int $status;

    /**
     * Constructor
     *
     * @param int            $statusCode
     * @param string         $message
     * @param int            $exceptionCode
     * @param Exception|null $previous
     */
    public function __construct(int $statusCode, string $message, int $exceptionCode = 0, Exception $previous = null)
    {
        parent::__construct($message, $exceptionCode, $previous);
        $this->status = $statusCode;
    }

    /**
     * Get the status code
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->status;
    }
}
