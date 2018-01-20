<?php

namespace JsonBrowser;

use Seld\JsonLint\JsonParser;

/**
 * Document context
 *
 * @since 1.5.0
 *
 * @package baacode/json-browser
 * @copyright (c) 2017-2018-2018 Erayd LTD
 * @author Steve Gilberd <steve@erayd.net>
 * @license ISC
 */
class Context
{
    /** Configuration options */
    private $options = 0;

    /** Root JsonBrowser object */
    private $root = null;
    
    /** Decoded JSON document */
    private $document = null;

    /**
     * Create a new instance
     *
     * @since 1.5.0
     *
     * @param JsonBrowser   $root       JsonBrowser for root node
     * @param string        $json       JSON-encoded data
     * @param int           $options    Configuration options (bitmask)
     */
    public function __construct(JsonBrowser $root, string $json, int $options = 0)
    {
        // set root
        $this->root = $root;

        // set options
        $this->options = $options;

        // decode document
        Exception::wrap(function () use ($json) {
            try {
                // decode via json_decode for speed
                $this->document = json_decode($json);
                if (json_last_error() != \JSON_ERROR_NONE) {
                    throw new \Exception(json_last_error_msg(), json_last_error());
                }
            } catch (\Throwable $e) {
                // if decoding fails, then lint using JsonParser
                $parser = new JsonParser();
                if (!is_null($parserException = $parser->lint($json))) {
                    throw $parserException;
                }
                // if JsonParser can decode successfully, but json_decode() cannot, complain loudly
                throw new \Exception('Unknown JSON decoding error'); // @codeCoverageIgnore
            }
        }, JsonBrowser::ERR_DECODING_ERROR, 'Unable to decode JSON data: %s');
    }

    /**
     * Check whether the value at a given path exists
     *
     * @since 1.5.0
     *
     * @param array $path Array of path elements
     * @return bool Whether a value exists at the given path
     */
    public function valueExists(array $path) : bool
    {
        $this->getValue($path, $exists);
        return $exists;
    }

    /**
     * Get the value at a given path
     *
     * @since 1.5.0
     *
     * @param array $path Array of path elements
     * @param bool Reference - set to true if the value exists, false otherwise
     * @return mixed|null Value data, or null if value does not exist
     */
    public function getValue(array $path, bool &$exists = null)
    {
        $target = $this->document;

        // follow path to conclusion or return null if not found
        while (count($path)) {
            $element = array_shift($path);
            if (is_array($target) && array_key_exists($element, $target)) {
                $target = $target[$element];
            } elseif (is_object($target) && property_exists($target, $element)) {
                $target = $target->$element;
            } else {
                $exists = false;
                return null;
            }
        }

        $exists = true;
        return $target;
    }

    /**
     * Set the value at a given path
     *
     * @since 1.5.0
     *
     * @param array $path Array of path elements
     * @param mixed $value Value data to set
     * @param bool  $padSparseArray Whether to left-pad sparse arrays with null values
     */
    public function setValue(array $path, $value, bool $padSparseArray = false)
    {
        $target = &$this->document;

        // follow path to conclusion and create missing elements
        while (count($path)) {
            $element = array_shift($path);
            $this->promoteContainer($target, $element);

            // step into child element
            if (is_array($target)) {
                // left-pad array with nulls
                if ($padSparseArray) {
                    for ($i = 0; $i < $element; $i++) {
                        if (!array_key_exists($element, $target)) {
                            $target[$i] = null;
                        }
                    }
                }
                if (!array_key_exists($element, $target)) {
                    $target[$element] = [];
                }
                $target = &$target[$element];
            } elseif (is_object($target)) {
                if (!property_exists($target, $element)) {
                    $target->$element = [];
                }
                $target = &$target->$element;
            } else {
                throw new Exception(
                    JsonBrowser::ERR_INVALID_CONTAINER_TYPE,
                    'Invalid container type: %s',
                    gettype($target)
                );
            }
        }

        // set value of target
        $target = $value;
    }

    /**
     * Delete the value at a given path
     *
     * @since 1.5.0
     *
     * @param array $path        Array of path elements
     * @param bool  $deleteEmpty Whether to delete empty containers
     */
    public function deleteValue(array $path, bool $deleteEmpty = false)
    {
        $target = &$this->document;
        $containerPath = [];

        // follow path to conclusion or return early if not found
        while (count($path) > 1) {
            $element = $containerPath[] = array_shift($path);
            if (is_array($target) && array_key_exists($element, $target)) {
                $target = &$target[$element];
            } elseif (is_object($target) && property_exists($target, $element)) {
                $target = &$target->$element;
            } else {
                return;
            }
        }

        if (count($path)) {
            // unset the child element
            if (is_array($target)) {
                unset($target[array_shift($path)]);
            } elseif (is_object($target)) {
                unset($target->{array_shift($path)});
            }

            // recurse to delete empty containers
            if ($deleteEmpty && !count((array)$target)) {
                $this->deleteValue($containerPath, $deleteEmpty);
            }
        } else {
            // we're at the root, so set the target to null rather than unsetting it
            $target = null;
        }
    }

    /**
     * Promote container type as necessary to hold a child key
     *
     * @since 1.5.0
     *
     * @param mixed $container  Target container
     * @param mixed $key        Intended key
     */
    private function promoteContainer(&$container, $key)
    {
        // promote null to array
        if (is_null($container)) {
            $container = [];
        }

        // promote array to object if the key is not an integer
        if (is_array($container) && !(is_numeric($key) && $key == floor($key))) {
            $container = (object)$container;
        }
    }
}
