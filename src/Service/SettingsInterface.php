<?php

namespace App\Service;

/**
 * Interface SettingsInterface
 */
interface SettingsInterface
{
    /**
     * Get settings by key
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get(string $key);
}
