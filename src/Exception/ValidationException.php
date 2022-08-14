<?php

namespace App\Exception;

use App\Util\ValidationResult;
use RuntimeException;

/**
 * Class ValidationException.
 */
class ValidationException extends RuntimeException
{
    private ValidationResult $validationResult;

    /**
     * Constructor.
     *
     * @param ValidationResult $validationResult
     */
    public function __construct(ValidationResult $validationResult)
    {
        parent::__construct($validationResult->getMessage());

        $this->validationResult = $validationResult;
    }

    /**
     * Get the validation result.
     *
     * @return ValidationResult
     */
    public function getValidationResult(): ValidationResult
    {
        return $this->validationResult;
    }
}
