<?php

namespace App\Service\Validation;

use App\Util\ValidationResult;

/**
 * Class AppValidation
 */
abstract class AppValidation
{
    /**
     * Validate the minimum length of a string.
     *
     * @param string $value
     * @param string $field
     * @param ValidationResult $ValidationContext
     * @param int $length
     */
    protected function validateLengthMin(string $value, string $field, ValidationResult $ValidationContext, int $length)
    {
        if (strlen(trim($value)) < $length) {
            $ValidationContext->setError($field, sprintf(__('Minimum length is %s'), $length));
        }
    }

    /**
     * Validate the maximum length of a string.
     *
     * @param string $value
     * @param string $field
     * @param ValidationResult $ValidationContext
     * @param int $length
     */
    protected function validateLengthMax(string $value, string $field, ValidationResult $ValidationContext, int $length)
    {
        if (strlen(trim($value)) > $length) {
            $ValidationContext->setError($field, sprintf(__('Maximum length is %s'), $length));
        }
    }
}
