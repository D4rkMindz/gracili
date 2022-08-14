<?php

namespace App\Repository;

/**
 * Class AppRepository.
 */
abstract class AppRepository
{
    /**
     * Format an array for returning it.
     *
     * @param mixed $array
     *
     * @return array
     * @deprecated
     */
    protected function format($array): array
    {
        $tmp = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $value = $this->format($value);
            }
            if (strpos($key, '/') !== false) {
                $keys = explode('/', $key);
                $index = array_shift($keys);
                $x = array_make_multidimensional($keys, $value);
                if (!array_key_exists($index, $tmp)) {
                    $tmp[$index] = [];
                }
                $tmp[$index] = array_merge($tmp[$index], $x);
            } else {
                $tmp[$key] = $value;
            }
        }

        return $tmp;
    }
}
