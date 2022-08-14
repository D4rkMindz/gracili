<?php

namespace App\Service\ID;

use Exception;
use Ramsey\Uuid\Uuid as UuidGenerator;

/**
 * Class UUID
 */
class UUID
{
    /**
     * Generate a UUID
     *
     * @return string
     * @throws Exception
     */
    public static function generate()
    {
        return UuidGenerator::uuid4()->toString();
    }
}
