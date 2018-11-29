<?php

namespace App\Util;

/**
 * Class ValidationContext.
 */
class ValidationResult
{
    protected $message;
    protected $errors = [];

    /**
     * ValidationContext constructor.
     *
     * @param string $message
     */
    public function __construct(string $message = 'Please check your data')
    {
        $this->message = $message === 'Please check your data' ? 'Please check your data' : $message;
    }

    /**
     * Get message.
     *
     * @return null|string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set message.
     *
     * @param string $message
     */
    public function setMessage(string $message)
    {
        $this->message = $message;
    }

    /**
     * Set error.
     *
     * @param string $field
     * @param string $message
     */
    public function setError(string $field, string $message)
    {
        $this->errors[] = [
            'field' => $field,
            'message' => $message,
        ];
    }

    /**
     * Get errors.
     *
     * @return array $errors
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Fail.
     *
     * Check if there are any errors
     *
     * @return bool
     */
    public function fails()
    {
        return !empty($this->errors);
    }

    /**
     * Success.
     *
     * Check if there are not any errors.
     *
     * @return bool
     */
    public function success()
    {
        return empty($this->errors);
    }

    /**
     * Clear.
     *
     * Clear message and errors
     */
    public function clear()
    {
        $this->message = null;
        $this->errors = [];
    }

    /**
     * Validation To Array.
     *
     * Get Validation Context as array
     *
     * @return array $result
     */
    public function toArray()
    {
        $result = [
            'message' => $this->message,
            'errors' => $this->errors,
        ];

        return $result;
    }
}
