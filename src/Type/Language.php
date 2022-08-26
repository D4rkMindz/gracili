<?php

namespace App\Type;

/**
 * Class Language
 */
class Language
{
    public const DEFAULT = 'en_GB';
    public const DE_CH = 'de_CH';
    public const FR_CH = 'fr_CH';
    public const EN_GB = 'en_GB';

    /**
     * Turn a string into a language tag (if found)
     *
     * @param string    $string
     * @param bool|null $returnFalseOnNotFound
     *
     * @return bool|string
     */
    public static function fromString(string $string, ?bool $returnFalseOnNotFound = false): bool|string
    {
        $whitelist = [
            'de' => Language::DE_CH,
            'de_CH' => Language::DE_CH,
            'de-CH' => Language::DE_CH,
            'de_DE' => Language::DE_CH,
            'de-DE' => Language::DE_CH,
            'de_AU' => Language::DE_CH,
            'de-AU' => Language::DE_CH,
            'fr' => Language::FR_CH,
            'fr_CH' => Language::FR_CH,
            'fr-CH' => Language::FR_CH,
            'fr_FR' => Language::FR_CH,
            'fr-FR' => Language::FR_CH,
            'en' => Language::EN_GB,
            'en_GB' => Language::EN_GB,
            'en-GB' => Language::EN_GB,
        ];

        if (isset($whitelist[$string])) {
            return $whitelist[$string];
        }

        if ($returnFalseOnNotFound) {
            return false;
        }

        return Language::DEFAULT;
    }
}