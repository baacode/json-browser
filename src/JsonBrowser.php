<?php

namespace JsonBrowser;

use Seld\JsonLint\JsonParser;

/**
 * Helper class for working with JSON-encoded data
 *
 * @since 1.0.0
 *
 * @package baacode/json-browser
 * @copyright (c) 2017 Erayd LTD
 * @author Steve Gilberd <steve@erayd.net>
 * @license ISC
 */
class JsonBrowser implements \IteratorAggregate
{
    /** Throw exceptions instead of using NULL for nonexistent children & siblings */
    const OPT_NONEXISTENT_EXCEPTIONS = 1;

    /** Error decoding JSON data */
    const ERR_DECODING_ERROR = 1;

    /** Encountered an unknown type */
    const ERR_UNKNOWN_TYPE = 2;

    /** Unknown child */
    const ERR_UNKNOWN_CHILD = 3;

    /** Unknown sibling */
    const ERR_UNKNOWN_SIBLING = 4;

    /** NULL type */
    const TYPE_NULL = 1;

    /** Boolean type */
    const TYPE_BOOLEAN = 2;

    /** String type */
    const TYPE_STRING = 4;

    /** Number type */
    const TYPE_NUMBER = 8;

    /** Integer type (subset of TYPE_NUMBER) */
    const TYPE_INTEGER = 16;

    /** Array type */
    const TYPE_ARRAY = 32;

    /** Object type */
    const TYPE_OBJECT = 64;

    /** Configuration options */
    private $options = 0;

    /** Decoded JSON document */
    private $document = null;

    /** Root browser node */
    private $root = null;

    /** Parent browser node */
    private $parent = null;

    /** Node path */
    private $path = [];

    /** Node key */
    private $key = null;

    /** Whether this node exists */
    private $exists = true;

    /**
     * Create a new instance
     *
     * @since 1.0.0
     *
     * @param string $json JSON-encoded data
     * @param int $options Configuration options (bitmask)
     */
    public function __construct(string $json, int $options = 0)
    {
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
        }, self::ERR_DECODING_ERROR, 'Unable to decode JSON data: %s');

        // set root node
        $this->root = $this;
    }

    /**
     * Check whether a child element exists
     *
     * @since 1.0.0
     *
     * @param mixed $key Index key
     * @return bool Whether the given child exists
     */
    public function childExists($key) : bool
    {
        $documentValue = $this->getValue();

        if (is_array($documentValue)) {
            return array_key_exists($key, $documentValue);
        } elseif (is_object($documentValue)) {
            return property_exists($documentValue, $key);
        }

        // non-container types cannot contain children
        return false;
    }

    /**
     * Get a child node
     *
     * @since 1.0.0
     *
     * @param mixed $key Index key
     * @return self Child node
     */
    public function getChild($key) : self
    {
        $documentValue = $this->getValue();

        if ($this->childExists($key)) {
            $child = clone $this;
            if (is_array($documentValue)) {
                $child->document = $documentValue[$key];
            } elseif (is_object($documentValue)) {
                $child->document = $documentValue->$key;
            }
        } elseif ($this->options & self::OPT_NONEXISTENT_EXCEPTIONS) {
            throw new Exception(self::ERR_UNKNOWN_CHILD, 'Unknown child: %s', $key);
        } else {
            $child = clone $this;
            $child->document = null;
            $child->exists = false;
        }

        $child->parent = $this;
        $child->path[] = strtr($key, ['~' => '~0', '/' => '~1', '%' => '%25']);
        $child->key = $key;

        return $child;
    }

    /**
     * Get an iterator handle
     *
     * @since 1.3.0
     *
     * @return Iterator Iterator instance
     */
    public function getIterator() : Iterator
    {
        return new Iterator($this);
    }

    /**
     * Get the JSON source for the current node
     *
     * @since 1.2.0
     *
     * @param int $options Bitwise options for json_encode()
     * @return string Encoded JSON string
     */
    public function getJSON(int $options = \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE)
    {
        return json_encode($this->getValue(), $options);
    }

    /**
     * Get the node index key (i.e. the child name within the parent node)
     *
     * @since 1.3.0
     *
     * @return mixed Index key within parent node, or null if this is the root
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Get the node at a given path
     *
     * @since 1.0.0
     *
     * @param string $path JSON pointer to the requested node
     * @return self
     */
    public function getNodeAt(string $path) : self
    {
        // fast-path for references to the root node
        if ($path == '#/') {
            return $this->root;
        }

        // decode pointer
        $path = array_map(function ($element) {
            return strtr($element, ['%25' => '%', '~1' => '/', '~0' => '~']);
        }, explode('/', substr($path, 2)));

        // walk path from root
        $node = $this->root;
        while (count($path)) {
            $node = $node->getChild(array_shift($path));
        }

        return $node;
    }

    /**
     * Get parent node
     *
     * @since 1.0.0
     *
     * @return self|null Parent node
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Get the node path
     *
     * @since 1.0.0
     *
     * @return string The node path as a JSON pointer
     */
    public function getPath() : string
    {
        return '#/' . implode('/', $this->path);
    }

    /**
     * Get root node
     *
     * @since 1.0.0
     *
     * @return self Root node
     */
    public function getRoot() : self
    {
        return $this->root;
    }

    /**
     * Get a sibling node
     *
     * @since 1.0.0
     *
     * @param mixed $key Index key
     * @return self
     */
    public function getSibling($key) : self
    {
        if (!$this->siblingExists($key)) {
            if ($this->options & self::OPT_NONEXISTENT_EXCEPTIONS) {
                throw new Exception(self::ERR_UNKNOWN_SIBLING, 'Unknown sibling: %s', $key);
            }
        }

        return $this->parent->getChild($key);
    }

    /**
     * Get the document value type
     *
     * @since 1.0.0
     *
     * @return int
     */
    public function getType() : int
    {
        $documentValue = $this->getValue();

        if (is_null($documentValue)) {
            return self::TYPE_NULL;
        }

        if (is_bool($documentValue)) {
            return self::TYPE_BOOLEAN;
        }

        if (is_string($documentValue)) {
            return self::TYPE_STRING;
        }

        if (is_numeric($documentValue)) {
            $type = self::TYPE_NUMBER;
            if (is_int($documentValue) || $documentValue == floor($documentValue)) {
                $type |= self::TYPE_INTEGER;
            }
            return $type;
        }

        if (is_array($documentValue)) {
            return self::TYPE_ARRAY;
        }

        if (is_object($documentValue)) {
            return self::TYPE_OBJECT;
        }

        throw new Exception(self::ERR_UNKNOWN_TYPE, 'Unknown type: %s', gettype($documentValue)); // @codeCoverageIgnore
    }

    /**
     * Get the document value
     *
     * @since 1.0.0
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->document;
    }

    /**
     * Get the value at a given path
     *
     * @since 1.1.0
     *
     * @param string $path JSON pointer to the requested node
     * @return mixed Value at the given path
     */
    public function getValueAt(string $path)
    {
        return $this->getNodeAt($path)->getValue();
    }

    /**
     * Test whether the document value is equal to a given value
     *
     * @since 1.4.0
     *
     * @param self|mixed $value Value to compare against
     * @return bool
     */
    public function isEqualTo($value) : bool
    {
        // unroll JsonBrowser objects
        if (is_object($value) && $value instanceof self) {
            $value = $value->getValue();
        }

        // test equality
        return $this->compare($this->getValue(), $value);
    }

    /**
     * Test whether the document value is *not* of a given type
     *
     * @since 1.3.0
     *
     * @param int $types Types to test for
     * @return bool Whether the type does not match
     */
    public function isNotType(int $types) : bool
    {
        return ($this->getType() & $types) == 0;
    }

    /**
     * Test whether the document value is of a given type
     *
     * @since 1.3.0
     *
     * @param int $types Types to test for
     * @param int $all Whether to require all types, or just one
     * @return bool Whether the type matches
     */
    public function isType(int $types, bool $all = false)
    {
        if ($all) {
            return ($this->getType() & $types) == $types;
        }
        return (bool)($this->getType() & $types);
    }

    /**
     * Check whether the current node exists in the parent document
     *
     * @since 1.4.0
     *
     * @return bool
     */
    public function nodeExists() : bool
    {
        return $this->exists;
    }

    /**
     * Check whether a sibling exists
     *
     * @since 1.0.0
     *
     * @param mixed $key Index key
     * @return bool Whether the sibling exists
     */
    public function siblingExists($key) : bool
    {
        // root nodes have no siblings
        if (is_null($this->parent)) {
            return false;
        }

        return $this->parent->childExists($key);
    }

    /**
     * Recursively compare two values for equality
     *
     * @since 1.4.0
     *
     * @param mixed $valueOne
     * @param mixed $valueTwo
     * @return bool
     */
    private function compare($valueOne, $valueTwo) : bool
    {

        // fast-path for type-equal (or instance-equal) values
        if ($valueOne === $valueTwo) {
            return true;
        }

        // recursive object comparison
        if (is_object($valueOne) && is_object($valueTwo)) {
            foreach ($valueOne as $pName => $pValue) {
                if (!property_exists($valueTwo, $pName) || !$this->compare($valueOne->$pName, $valueTwo->$pName)) {
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

        // compare numeric types loosely, but don't accept numeric strings
        if (!is_string($valueOne) && !is_string($valueTwo) && is_numeric($valueOne) && is_numeric($valueTwo)) {
            return ($valueOne == $valueTwo);
        }

        // strict equality check failed
        return false;
    }
}
