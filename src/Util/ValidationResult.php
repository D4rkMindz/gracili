<?php

namespace App\Util;

/**
 * Class ValidationContext.
 */
class ValidationResult
{
    public const IGNORE_FOR_VALIDATION = null;
    protected ?string $message;
    protected ?array $errors = [];

    /**
     * ValidationContext constructor.
     *
     * @param string|null $message
     */
    public function __construct(?string $message = 'Please check your data')
    {
        $this->message = $message === 'Please check your data' ? 'Please check your data' : $message;
    }

    /**
     * Get message.
     *
     * @return string|null
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * Set message.
     *
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * Set error.
     *
     * @param string $field
     * @param string $message
     */
    public function setError(string $field, string $message): void
    {
        $this->errors[] = [
            'field' => $field,
            'message' => $message,
        ];
    }

    /**
     * Get all errors for specified field
     *
     * @param string $field
     *
     * @return array
     */
    public function getErrorsForField(string $field): array
    {
        $fieldErrors = [];
        foreach ($this->getErrors() as $error) {
            if ($error['field'] === $field) {
                $fieldErrors[] = $error;
            }
        }

        return $fieldErrors;
    }

    /**
     * Get errors.
     *
     * @return array $errors
     */
    public function getErrors(): ?array
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
    public function fails(): bool
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
    public function success(): bool
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
    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'errors' => $this->errors,
        ];
    }

    /**
     * Get a one dimensional array like 'field'
     *
     * @return string[]
     */
    public function toOneDimensionalArray(): array
    {
        $result = [];
        $errors = $this->getErrors();
        $count = count($errors);

        foreach ($errors as $i => $error) {
            if (!isset($result[$error['field']])) {
                $result[$error['field']] = '';
            }

            $result[$error['field']] .= $error['message'];

            if (++$i < $count) {
                $result[$error['field']] .= PHP_EOL;
            }
        }

        return $result;
    }
}
