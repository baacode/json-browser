<?php

namespace JsonBrowser;

/**
 * Static utility methods
 *
 * @internal
 * @since 1.5.0
 *
 * @package baacode/json-browser
 * @copyright (c) 2017-2018-2018 Erayd LTD
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

        if (array_keys($valueOne) != array_keys($valueTwo)) {
            return false;
        }

        foreach ($valueOne as $key => $value) {
            if (!self::compare($value, $valueTwo[$key])) {
                return false;
            }
        }

        return true;
    }
}
