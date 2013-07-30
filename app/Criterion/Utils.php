<?php

namespace Criterion;

class Utils
{
    /**
     * Multi Dimensional Array Sorting
     */
    public static function array_sort_func($a, $b = null)
    {
        static $keys;
        if ($b === null) {
            return $keys = $a;
        }
        foreach ($keys as $k) {
            if ($k[0] === '!') {
                $k = substr($k, 1);
                if ($a[$k] !== $b[$k]) {
                    return is_numeric($a[$k])
                        ? $b[$k] - $a[$k]
                        : strcasecmp($b[$k], $a[$k]);
                }
            } elseif ($a[$k] !== $b[$k]) {
                return is_numeric($a[$k])
                    ? $a[$k] - $b[$k]
                    : strcasecmp($a[$k], $b[$k]);
            }
        }

        return 0;
    }
    public static function array_sort (&$array)
    {
        $keys = func_get_args();
        if (! $array) {
            return $keys;
        }
        array_shift($keys);
        self::array_sort_func($keys);
        usort($array, 'Criterion\\Utils::array_sort_func');
    }

    /**
     * Multi Dimensional Object Sorting (Experimental)
     */
    public static function object_sort_func($a, $b = null)
    {
        static $keys;
        if ($b === null) {
            return $keys = $a;
        }
        foreach ($keys as $k) {
            if ($k[0] === '!') {
                $k = substr($k, 1);
                if ($a->$k !== $b->$k) {
                    return is_numeric($a->$k)
                        ? $b->$k - $a->$k
                        : strcasecmp($b->$k, $a->$k);
                }
            } elseif ($a->$k !== $b->$k) {
                return is_numeric($a->$k)
                    ? $a->$k - $b->$k
                    : strcasecmp($a->$k, $b->$k);
            }
        }

        return 0;
    }
    public static function object_sort(&$array)
    {
        $keys = func_get_args();
        if (! $array) {
            return $keys;
        }
        array_shift($keys);
        self::object_sort_func($keys);
        usort($array, 'Criterion\\Utils::object_sort_func');
    }

    /**
     * Array merge recursive
     */
    public static function array_merge(array &$array1, array &$array2)
    {
        $merged = $array1;
        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = self::array_merge($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

}
