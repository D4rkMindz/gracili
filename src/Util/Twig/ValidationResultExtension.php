<?php

namespace App\Util\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class ErrorForExtension
 */
class ValidationResultExtension extends AbstractExtension
{
    /**
     * @return array|TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('errors_for', [$this, 'errorsFor'], ['is_safe' => ['html']]),
            new TwigFunction('has_errors', [$this, 'hasErrors']),
        ];
    }

    /**
     * Get all errors from a ValidationResult for field
     *
     * @param string $field
     * @param array  $errors
     * @param string $break
     *
     * @return string
     */
    public function errorsFor(string $field, array $errors, string $break = '<br />'): string
    {
        $errorsAsString = '';
        foreach ($errors as $error) {
            if (strtolower($error['field']) === strtolower($field)) {
                $errorsAsString .= $error['message'] . $break;
            }
        }

        return $errorsAsString;
    }

    /**
     * Check if a field has errors
     *
     * @param string $field
     * @param array  $errors
     *
     * @return bool
     */
    public function hasErrors(string $field, array $errors): bool
    {
        foreach ($errors as $error) {
            if (strtolower($error['field']) === strtolower($field)) {
                return true;
            }
        }

        return false;
    }
}
