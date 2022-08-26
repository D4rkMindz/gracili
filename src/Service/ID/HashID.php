<?php

namespace App\Service\ID;

use Hashids\Hashids;

/**
 * Class HashID
 */
class HashID
{
    private const MIN_LENGTH = 5;
    private const SALT = '';

    /**
     * Decode a single ID Hash
     *
     * @param string $hash
     *
     * @return int
     */
    public static function decodeSingle(string $hash): int
    {
        $hashes = self::decode($hash);

        if (isset($hashes[0])) {
            return (int)$hashes[0];
        }

        return -1;
    }

    /**
     * Decode a Hash to an array of IDs
     *
     * @param string $hash
     *
     * @return array
     */
    public static function decode(string $hash): array
    {
        return (new Hashids(self::SALT, self::MIN_LENGTH))->decode($hash);
    }

    /**
     * Encode a record recursively
     *
     * @param array $array
     *
     * @return array
     */
    public static function encodeRecord(array $array): array
    {
        foreach ($array as $field => $value) {
            if (is_array($value)) {
                $array[$field] = self::encodeRecord($value);
                continue;
            }
            if (($field === 'id' || str_ends_with($field, '_id')) && is_numeric($value)) {
                $array[$field] = self::encode($value);
            }
        }

        return $array;
    }

    /**
     * Encode an ID to a Hash
     *
     * @param int|array $id
     *
     * @return string
     */
    public static function encode($id)
    {
        return (new Hashids(self::SALT, self::MIN_LENGTH))->encode($id);
    }
}