<?php

namespace App\Exception;

use Exception;
use Throwable;

/**
 * Record Exception (for record exceptions
 */
abstract class RecordException extends Exception
{
    private ?string $locator;
    private ?string $userMessage;

    /**
     * Constructor
     *
     * @param string         $message
     * @param string|null    $locator The ID of the record that was tried to fetch
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message, string $locator = null, int $code = 255, Throwable $previous = null)
    {
        parent::__construct($message . ' ' . $locator, $code, $previous);
        $this->userMessage = $message;
        $this->locator = $locator;
    }

    /**
     * Get the user message
     *
     * @return string|null
     */
    public function getUserMessage(): ?string
    {
        return $this->userMessage;
    }

    /**
     * Get the ID
     *
     * @return string|null
     */
    public function getLocator(): ?string
    {
        return $this->locator;
    }
}