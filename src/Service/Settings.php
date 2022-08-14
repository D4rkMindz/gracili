<?php

namespace App\Service;

/**
 * Class Settings
 */
class Settings implements SettingsInterface
{
    private array $settings;

    /**
     * Settings constructor.
     *
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }


    /**
     * Get settings by key
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->settings[$key];
    }
}
