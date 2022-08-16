<?php

namespace App\Exception;

use InvalidArgumentException;
use Throwable;

/**
 * Class OAuthException
 */
class OAuthException extends InvalidArgumentException
{
    /**
     * Constructor
     *
     * @param string|null    $message
     * @param int|null       $code
     * @param Throwable|null $previous
     */
    public function __construct(?string $message = "", ?int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}