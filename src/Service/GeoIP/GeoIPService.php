<?php

namespace App\Service\GeoIP;

use App\Service\SettingsInterface;
use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use MaxMind\Db\Reader\InvalidDatabaseException;

/**
 * Class GeoIPService
 */
class GeoIPService
{
    private Reader $reader;

    /**
     * Constructor.
     *
     * @throws InvalidDatabaseException
     */
    public function __construct(SettingsInterface $settings)
    {
        $config = $settings->get(GeoIPService::class);
        $this->reader = new Reader($config['database']);
    }

    /**
     * Get the location
     *
     * @param string $ip
     *
     * @return string
     * @throws InvalidDatabaseException
     */
    public function getLocation(string $ip): string
    {
        try {
            $record = $this->reader->city($ip);

            return "{$record->city->name} ({$record->country->name}, {$record->continent->name})";
        } catch (AddressNotFoundException $exception) {
            return __('Unknown location');
        }
    }
}