<?php

namespace App\Util;

use App\Exception\RecordNotFoundException;
use App\Service\ID\HashID;

/**
 * Class ArgExtract
 */
class ArgExtract
{
    /**
     * Extract the cell ID from the route arguments
     *
     * @param array $args
     *
     * @return int
     * @throws RecordNotFoundException
     */
    public static function EVENT_ID(array $args): int
    {
        $eventHash = (string)$args['event_hash'];

        $eventId = HashID::decodeSingle($eventHash);
        if ($eventId === -1) {
            throw new RecordNotFoundException(__('This event was not found in our database'), (string)$eventId);
        }

        return $eventId;
    }
}
