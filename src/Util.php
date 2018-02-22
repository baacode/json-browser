<?php

namespace JsonBrowser;

/**
 * Static utility methods
 *
 * @internal
 * @since 1.5.0
 *
 * @package baacode/json-browser
 * @copyright (c) 2017-2018 Erayd LTD
 * @author Steve Gilberd <steve@erayd.net>
 * @license ISC
 */
abstract class Util
{
    /**
     * Decode a JSON pointer to a path array
     *
     * @since 1.5.0
     *
     * @param string $pointer JSON pointer
     * @return array Array of path elements
     */
    public static function decodePointer(string $pointer) : array
    {
        // root pointer is an empty array, *not* an array with one empty element
        if ($pointer == '#/') {
            return [];
        }

        // explode path portion of pointer and translate each element
        return array_map(function ($element) {
            return strtr($element, ['%25' => '%', '~1' => '/', '~0' => '~']);
        }, explode('/', substr($pointer, 2)));
    }

    /**
     * Encode a path array as a JSON pointer
     *
     * @since 1.5.0
     *
     * @param array $path Array of path elements
     * @return string JSON pointer
     */
    public static function encodePointer(array $path) : string
    {
        // translate each element of path & implode to pointer
        return '#/' . implode('/', array_map(function ($element) {
            return strtr($element, ['~' => '~0', '/' => '~1', '%' => '%25']);
        }, $path));
    }

    /**
     * Compare two values for equality
     *
     * @since 1.4.0 (formerly JsonBrowser::compare())
     *
     * @param mixed $valueOne
     * @param mixed $valueTwo
     * @return bool
     */
    public static function compare($valueOne, $valueTwo) : bool
    {

        // fast-path for type-equal (or instance-equal) values
        if ($valueOne === $valueTwo) {
            return true;
        }

        // recursive object / array comparison
        if (is_object($valueOne) && is_object($valueTwo)) {
            return self::compareObjects($valueOne, $valueTwo);
        } elseif (is_array($valueOne) && is_array($valueTwo)) {
            return self::compareArrays($valueOne, $valueTwo);
        }

        // compare numeric types loosely, but don't accept numeric strings
        if (!is_string($valueOne) && !is_string($valueTwo) && is_numeric($valueOne) && is_numeric($valueTwo)) {
            return ($valueOne == $valueTwo);
        }

        // strict equality check failed
        return false;
    }

    /**
     * Recursively compare two objects for equality
     *
     * @since 1.5.0
     *
     * @param \StdClass $valueOne
     * @param \StdClass $valueTwo
     * @return bool
     */
    private static function compareObjects(\StdClass $valueOne, \StdClass $valueTwo)
    {
        foreach ($valueOne as $pName => $pValue) {
            if (!property_exists($valueTwo, $pName) || !self::compare($valueOne->$pName, $valueTwo->$pName)) {
                return false;
            }
        }
        foreach ($valueTwo as $pName => $pValue) {
            if (!property_exists($valueOne, $pName)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Recursively compare two arrays for equality
     *
     * @since 2.1.1
     *
     * @param array $valueOne
     * @param array $valueTwo
     * @return bool
     */
    private static function compareArrays(array $valueOne, array $valueTwo) : bool
    {
        if (count($valueOne) != count($valueTwo)) {
            return false;
        }

        foreach ($valueOne as $key => $value) {
            if (!self::compare($value, $valueTwo[$key])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the type mask for a given value
     *
     * @since 2.4.0 (was JsonBrowser::getType() in previous versions)
     *
     * @param mixed $value   Value to test
     * @param bool  $onlyOne Whether to set only one type (the most specific)
     * @return int Type mask
     */
    public static function typeMask($value, bool $onlyOne = false) : int
    {
        if (is_null($value)) {
            return JsonBrowser::TYPE_NULL;
        }

        if (is_bool($value)) {
            return JsonBrowser::TYPE_BOOLEAN;
        }

        if (is_string($value)) {
            return JsonBrowser::TYPE_STRING;
        }

        if (is_numeric($value)) {
            $type = JsonBrowser::TYPE_NUMBER;
            if (is_int($value) || $value == floor($value)) {
                if ($onlyOne) {
                    $type = JsonBrowser::TYPE_INTEGER;
                } else {
                    $type |= JsonBrowser::TYPE_INTEGER;
                }
            }
            return $type;
        }

        if (is_array($value)) {
            return JsonBrowser::TYPE_ARRAY;
        }

        if (is_object($value)) {
            return JsonBrowser::TYPE_OBJECT;
        }

        throw new Exception(JsonBrowser::ERR_UNKNOWN_TYPE, 'Unknown type: %s', gettype($value)); // @codeCoverageIgnore
    }

    /**
     * Cast a value to conform to the given type mask, losing as little fidelity as possible
     *
     * @since 2.4.0
     *
     * @param int $asType  The type mask to cast to
     * @param mixed $value The value to cast
     * @return mixed The cast value
     */
    public static function cast(int $asType, $value)
    {
        // get the value type
        $type = self::typeMask($value);

        // check whether value is already one of the desired types
        if ($type & $asType) {
            return $value;
        }

        // cast objects & arrays
        // -> directly to an object or associative array
        // -> to a json-encoded string
        // -> to an integer count of the members
        // -> to a boolean indicating whether any members are present
        if ($type & (JsonBrowser::TYPE_OBJECT | JsonBrowser::TYPE_ARRAY)) {
            if ($asType & JsonBrowser::TYPE_OBJECT) {
                return (object) $value;
            } elseif ($asType & JsonBrowser::TYPE_ARRAY) {
                return (array) $value;
            } elseif ($asType & JsonBrowser::TYPE_STRING) {
                return json_encode($value, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE);
            } elseif ($asType & (JsonBrowser::TYPE_NUMBER | JsonBrowser::TYPE_INTEGER)) {
                return count((array)$value);
            } elseif ($asType & JsonBrowser::TYPE_BOOLEAN) {
                return (bool) count((array)$value);
            }
        }

        // cast strings
        // -> to an array of unicode characters
        // -> to an array of unicode characters, cast as an object (stdClass)
        // -> to an integer count of [unicode] characters in the string
        // -> to a boolean indicating whether the string length is greater than zero
        if ($type & JsonBrowser::TYPE_STRING) {
            if ($asType & JsonBrowser::TYPE_ARRAY) {
                return preg_split('//u', $value, null, \PREG_SPLIT_NO_EMPTY);
            } elseif ($asType & JsonBrowser::TYPE_OBJECT) {
                return (object) preg_split('//u', $value, null, \PREG_SPLIT_NO_EMPTY);
            } elseif ($asType & (JsonBrowser::TYPE_NUMBER | JsonBrowser::TYPE_INTEGER)) {
                return mb_strlen($value);
            } elseif ($asType & JsonBrowser::TYPE_BOOLEAN) {
                return (bool) strlen($value);
            }
        }

        // cast numbers
        // -> to an integer (or float if larger than PHP_INT_MAX), with the fractional component discarded
        // -> to a string representation of the number, in base-10
        // -> to the only member [0] of an array
        // -> to the 'value' property of an stdClass object
        // -> to a boolean indicating whether the number is non-zero
        if ($type & JsonBrowser::TYPE_NUMBER) {
            if ($asType & JsonBrowser::TYPE_INTEGER) {
                $int = $value > 0 ? floor($value) : ceil($value);
                return abs($int) > \PHP_INT_MAX ? $int : (int) $int;
            } elseif ($asType & JsonBrowser::TYPE_STRING) {
                return json_encode($value);
            } elseif ($asType & JsonBrowser::TYPE_ARRAY) {
                return [$value];
            } elseif ($asType & JsonBrowser::TYPE_OBJECT) {
                return (object) ['value' => $value];
            } elseif ($asType & JsonBrowser::TYPE_BOOLEAN) {
                return $value != 0;
            }
        }

        // cast booleans
        // -> to an integer
        // -> to a true / false string
        // -> to the only member [0] of an array
        // -> to the 'value' property of an stdClass object
        if ($type & JsonBrowser::TYPE_BOOLEAN) {
            if ($asType & (JsonBrowser::TYPE_NUMBER | JsonBrowser::TYPE_INTEGER)) {
                return (int)$value;
            } elseif ($asType & JsonBrowser::TYPE_STRING) {
                return json_encode($value);
            } elseif ($asType & JsonBrowser::TYPE_ARRAY) {
                return [$value];
            } elseif ($asType & JsonBrowser::TYPE_OBJECT) {
                return (object) ['value' => $value];
            }
        }

        // cast nulls
        // -> to a boolean false
        // -> to an integer zero
        // -> to an empty string
        // -> to an empty array
        // -> to an empty stdClass object
        if ($type & JsonBrowser::TYPE_NULL) {
            if ($asType & JsonBrowser::TYPE_BOOLEAN) {
                return false;
            } elseif ($asType & (JsonBrowser::TYPE_NUMBER | JsonBrowser::TYPE_INTEGER)) {
                return 0;
            } elseif ($asType & JsonBrowser::TYPE_STRING) {
                return '';
            } elseif ($asType & JsonBrowser::TYPE_ARRAY) {
                return [];
            } elseif ($asType & JsonBrowser::TYPE_OBJECT) {
                return new \stdClass();
            }
        }

        // cast to null as a last resort, because it's a lossy constant
        if ($asType & JsonBrowser::TYPE_NULL) {
            return null;
        }

        // anything left over is an unknown type - should never be reached unless the user passes an invalid type
        throw new Exception(JsonBrowser::ERR_UNKNOWN_TYPE, 'Unknown value or cast type');
    }
}
