<?php

use Symfony\Component\Translation\Translator;

/**
 * Translation function (i18n).
 *
 * @param mixed $message
 * @return string
 */
function __($message)
{
    static $translator = null;
    /* @var $translator Translator */
    if ($message instanceof Translator) {
        $translator = $message;
        return '';
    }
    $translated = $translator->trans($message);
    $context = array_slice(func_get_args(), 1);
    if (!empty($context)) {
        $translated = vsprintf($translated, $context);
    }

    return $translated;
}

/**
 * Get array value or null.
 *
 * @param string $key
 * @param array $array
 * @return mixed|null
 */
function array_value(string $key, array $array)
{
    return array_key_exists($key, $array) ? $array[$key] : null;
}

/**
 * Make an array multidimensional based on the given keys.
 *
 * @param $keys
 * @param $resultValue
 * @return array
 */
function array_make_multidimensional($keys, $resultValue)
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
