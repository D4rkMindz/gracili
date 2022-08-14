<?php

use Symfony\Component\Translation\Translator;

/**
 * Translation function (i18n).
 *
 * @param string|Translator $message
 *
 * @return string
 */
function __($message): string
{
    static $translator = null;
    /* @var $translator Translator */
    if ($message instanceof Translator) {
        $translator = $message;

        return '';
    }
    $translated = $translator->trans($message);
    $ctx = array_slice(func_get_args(), 1);
    if (!empty($ctx)) {
        $context = $ctx[0];
        $find = array_keys($context);
        foreach ($find as $key => $value) {
            $find[$key] = '{' . $value . '}';
        }
        $replace = array_values($context);
        $translated = str_ireplace($find, $replace, $translated);
    }

    return $translated;
}

/**
 * Get array value or null.
 *
 * @param string     $key
 * @param array|null $array $array
 *
 * @return array|null
 */
function array_value(string $key, ?array $array): ?array
{
    return array_key_exists($key, $array) ? $array[$key] : null;
}

/**
 * Make an array multidimensional based on the given keys.
 *
 * @param mixed $keys
 * @param mixed $resultValue
 *
 * @return array
 */
function array_make_multidimensional($keys, $resultValue): array
{
    if (!is_array($keys)) {
        return $resultValue;
    }
    $tmp = [];
    $index = array_shift($keys);
    if (!isset($keys[0])) {
        $tmp[$index] = $resultValue;
    } else {
        $tmp[$index] = array_make_multidimensional($keys, $resultValue);
    }

    return $tmp;
}

/**
 * Recursively remove directory
 *
 * @param string $dir
 */
function rrmdir(string $dir)
{
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir . "/" . $object) == "dir") {
                    rrmdir($dir . "/" . $object);
                } else {
                    unlink($dir . "/" . $object);
                }
            }
        }
        reset($objects);
        rmdir($dir);
    }
}
