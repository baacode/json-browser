<?php

namespace JsonBrowser;

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

    /** Get node value instead of JsonBrowser object for __get() */
    const OPT_GET_VALUE = 2;

    /** Error decoding JSON data */
    const ERR_DECODING_ERROR = 1;

    /** Encountered an unknown type */
    const ERR_UNKNOWN_TYPE = 2;

    /** Unknown child */
    const ERR_UNKNOWN_CHILD = 3;

    /** Unknown sibling */
    const ERR_UNKNOWN_SIBLING = 4;

    /** Unknown self */
    const ERR_UNKNOWN_SELF = 5;

    /** Invalid container type */
    const ERR_INVALID_CONTAINER_TYPE = 6;

    /** Unknown target */
    const ERR_UNKNOWN_TARGET = 7;

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

    /** Document context */
    private $context = null;

    /** Root node */
    private $root = null;

    /** Node path */
    private $path = [];

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
        $this->root = $this;
        $this->options = $options;
        $this->context = new Context($this, $json, $options);
    }

    /**
     * Dynamically get child nodes or values as object properties
     *
     * @since 1.5.0
     *
     * @param self|mixed $key Index key
     */
    public function __get($key)
    {
        if ($this->options & self::OPT_GET_VALUE) {
            return $this->getChild($key)->getValue();
        } else {
            return $this->getChild($key);
        }
    }

    /**
     * Dynamically set child node values as object properties
     *
     * Will set the value on a child node, but will not pad sparse arrays.
     *
     * @since 1.5.0
     *
     * @param mixed $key Index key
     * @param mixed $value Value data to set
     */
    public function __set($key, $value)
    {
        $this->context->setValue(array_merge($this->path, [$key]), $value);
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
        return $this->context->valueExists(array_merge($this->path, [$key]));
    }

    /**
     * Delete the current node value
     *
     * @since 1.5.0
     *
     * @param bool $deleteEmpty Whether to delete empty containers
     */
    public function deleteValue(bool $deleteEmpty = false)
    {
        $this->context->deleteValue($this->path, $deleteEmpty);
    }

    /**
     * Delete the value at a given path
     *
     * @since 1.5.0
     *
     * @param string $path JSON pointer to the node that should be deleted
     * @param bool $deleteEmpty Whether to delete empty containers
     */
    public function deleteValueAt(string $path, bool $deleteEmpty = false)
    {
        $this->context->deleteValue(Util::decodePointer($path), $deleteEmpty);
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
        if (($this->options & self::OPT_NONEXISTENT_EXCEPTIONS) && !$this->childExists($key)) {
            throw new Exception(self::ERR_UNKNOWN_CHILD, 'Unknown child: %s', $key);
        }

        $child = clone $this;
        $child->path[] = $key;

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
        return count($this->path) ? end($this->path) : null;
    }

    /**
     * Get the node at a given path
     *
     * @since 1.0.0
     *
     * @param string $path JSON pointer to the requested node
     * @return self Node at the target path
     */
    public function getNodeAt(string $path) : self
    {
        $node = clone $this;
        $node->path = Util::decodePointer($path);

        if (($this->options & self::OPT_NONEXISTENT_EXCEPTIONS) && !$node->nodeExists()) {
            throw new Exception(self::ERR_UNKNOWN_TARGET, 'Target node is unknown: %s', $path);
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
        // the root node has no parent, so return null
        if (!count($this->path)) {
            return null;
        }
        
        $parent = clone $this;
        array_pop($parent->path);

        return $parent;
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
        return Util::encodePointer($this->path);
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
     * @return self Sibling node
     */
    public function getSibling($key) : self
    {
        if (($this->options & self::OPT_NONEXISTENT_EXCEPTIONS) && !$this->siblingExists($key)) {
            throw new Exception(self::ERR_UNKNOWN_SIBLING, 'Unknown sibling: %s', $key);
        }

        $sibling = clone $this;
        array_pop($sibling->path);
        $sibling->path[] = $key;

        return $sibling;
    }


    /**
     * Get the document value type
     *
     * @since 1.0.0
     *
     * @return int Bitmask list of applicable types (See JsonBrowser::TYPE_* constants)
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
     * Get the node value
     *
     * @since 1.0.0
     *
     * @return mixed Node value
     */
    public function getValue()
    {
        $value = $this->context->getValue($this->path, $exists);
        
        if (($this->options & self::OPT_NONEXISTENT_EXCEPTIONS) && !$exists) {
            throw new Exception(self::ERR_UNKNOWN_SELF, 'Current node is unknown: %s', $this->getPath());
        }

        return $value;
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
        $value = $this->context->getValue(Util::decodePointer($path), $exists);
        
        if (($this->options & self::OPT_NONEXISTENT_EXCEPTIONS) && !$exists) {
            throw new Exception(self::ERR_UNKNOWN_TARGET, 'Target node is unknown: %s', $path);
        }

        return $value;
    }

    /**
     * Test whether the document value is equal to a given value
     *
     * Comparisons considered equal:
     *  - normal strictly-typed equality (===);
     *  - loosely-typed equality (==) where both values are numeric and are *not* strings;
     *  - any object having the same number and value for all properties, as per the above tests.
     *
     * @since 1.4.0
     *
     * @param self|mixed $value Value to compare against
     * @return bool Whether the node value is equal to $value
     */
    public function isEqualTo($value) : bool
    {
        // unroll JsonBrowser objects
        if (is_object($value) && $value instanceof self) {
            $value = $value->getValue();
        }

        // test equality
        return Util::compare($this->getValue(), $value);
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
    public function isType(int $types, bool $all = false) : bool
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
     * @return bool Whether the current node exists
     */
    public function nodeExists() : bool
    {
        return $this->context->valueExists($this->path);
    }

    /**
     * Set the node value
     *
     * @since 1.4.0
     *
     * @param mixed $value          Data to set
     * @param bool  $padSparseArray Whether to left-pad sparse arrays with null values
     */
    public function setValue($value, bool $padSparseArray = false)
    {
        $this->context->setValue($this->path, $value, $padSparseArray);
    }

    /**
     * Set the value at a given path
     *
     * @since 1.4.0
     *
     * @param string $path           JSON pointer to the requested node
     * @param mixed  $value          Data to set
     * @param bool   $padSparseArray Whether to left-pad sparse arrays with null values
     */
    public function setValueAt(string $path, $value, bool $padSparseArray = false)
    {
        return $this->context->setValue(Util::decodePointer($path), $value, $padSparseArray);
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
        if (!count($this->path)) {
            return false;
        }

        return $this->getParent()->childExists($key);
    }
}
