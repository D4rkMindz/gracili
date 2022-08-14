<?php

namespace App\Service\Feature;

class Feature
{
    public const AUTH = 'auth';

    /**
     * Check if a feature is enabled
     *
     * @param string $flag
     *
     * @return bool
     */
    public static function isEnabled(string $flag): bool
    {
        $features = [];
        if (file_exists(__DIR__ . '/../../../config/features.php')) {
            $features = require __DIR__ . '/../../../config/features.php';
        }
        if (file_exists(__DIR__ . '/../../../../features.php')) {
            $features = require __DIR__ . '/../../../../features.php';
        }
        if (!isset($features[$flag])) {
            return false;
        }

        return (bool)$features[$flag];
    }
}