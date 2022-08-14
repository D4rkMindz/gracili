<?php

namespace App\Exception;

use Exception;
use Throwable;

/**
 * Class RecordNotFoundException.
 */
class RecordNotFoundException extends Exception
{
    private string $locator;

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
        $this->locator = $locator;
    }

    /**
     * Get the ID
     *
     * @return string
     */
    public function getLocator(): string
    {
        return $this->locator;
    }
}
