<?php

namespace JsonBrowser;

use Seld\JsonLint\JsonParser;

/**
 * Helper class for working with JSON-encoded data
 *
 * @since 1.0.0
 *
 * @package baacode/json-browser
 * @copyright (c) 2017-2018 Erayd LTD
 * @author Steve Gilberd <steve@erayd.net>
 * @license ISC
 */
class JsonBrowser implements \IteratorAggregate, \Countable
{
    /** Throw exceptions instead of using NULL for nonexistent children & siblings */
    const OPT_NONEXISTENT_EXCEPTIONS = 1;

    /** Get node value instead of JsonBrowser object for __get() */
    const OPT_GET_VALUE = 2;

    /** Treat the document definition passed to the constructor as JSON, and decode it */
    const OPT_DECODE = 4;

    /** Cast values with a non-matching type requirement, instead of throwing an exception */
    const OPT_CAST = 8;

    /** Default config options [none] */
    const OPT_DEFAULT = 0;

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

    /** The value type does not match the provided type mask */
    const ERR_INVALID_TYPE = 8;

    /** Cannot cast to undefined */
    const ERR_UNDEFINED_CAST = 9;

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

    /** Undefined type */
    const TYPE_UNDEFINED = 128;

    /** All defined types */
    const TYPE_ALL = ~0 & ~self::TYPE_UNDEFINED;

    /** Configuration options */
    private $options = 0;

    /** Document context */
    private $context = null;

    /** Node path */
    private $path = [];

    /** Whether the node exists during the current operation */
    private $currentlyExists = null;

    /**
     * Create a new instance
     *
     * @since 1.0.0
     *
     * @param int  $options   Configuration options (bitmask)
     * @param mixed $document Reference to the default document
     */
    public function __construct(int $options = self::OPT_DEFAULT, &$document = null)
    {
        $this->options = $options;
        if ($this->options & self::OPT_DECODE) {
            $this->loadJSON($document);
        } else {
            $this->attach($document);
        }
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
     * @param mixed $key   Index key
     * @param mixed $value Value data to set
     */
    public function __set($key, $value)
    {
        $this->context->setValue(array_merge($this->path, [$key]), $value);
    }

    /**
     * Get the current node as a document root
     *
     * @since 2.0.0
     *
     * @return self A new JsonBrowser instance pointing to the current node
     */
    public function asRoot() : self
    {
        $root = clone $this;
        $root->context = $this->context->getSubtreeContext($this->path);
        $root->path = [];

        return $root;
    }

    /**
     * Ensure that a value is of a given type
     *
     * @since 2.4.0
     *
     * @param int   $asType Required type mask
     * @param mixed $value  Value to check
     * @param bool  $cast   Whether to cast the value to a valid type
     * @return mixed The value, correctly typed
     */
    private function assertType(int $asType, $value, bool $cast = null)
    {
        if (!$this->currentlyExists && ($asType & self::TYPE_UNDEFINED)) {
            return $value;
        } elseif ($cast || (is_null($cast) && ($this->options & self::OPT_CAST))) {
            return Util::cast($asType, $value);
        } elseif (!($asType & Util::typeMask($value))) {
            throw new Exception(self::ERR_INVALID_TYPE, 'Value is not of the required type');
        }

        return $value;
    }

    /**
     * Attach to an existing decoded document
     *
     * @since 2.0.0
     *
     * @param mixed $document Reference to the target document
     */
    public function attach(&$document)
    {
        $this->context = new Context($document, $this->options);
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
     * Count the number of children contained within this node
     *
     * @since 2.5.0
     *
     * @return int The number of children within this node
     */
    public function count() : int
    {
        if ($this->isType(self::TYPE_OBJECT | self::TYPE_ARRAY)) {
            return count($this->getValue(self::TYPE_ARRAY, true));
        }

        return 0;
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
     * @param string $path        JSON pointer to the node that should be deleted
     * @param bool   $deleteEmpty Whether to delete empty containers
     */
    public function deleteValueAt(string $path, bool $deleteEmpty = false)
    {
        $this->context->deleteValue(Util::decodePointer($path), $deleteEmpty);
    }

    /**
     * Get single node annotation
     *
     * @since 2.1.0
     *
     * @param string $name Annotation name
     * @return mixed Most recently-set annotation matching the given name, or null if not set
     */
    public function getAnnotation(string $name)
    {
        $annotations = $this->getAnnotations($name);
        return count($annotations) ? end($annotations) : null;
    }

    /**
     * Get node annotations
     *
     * @since 2.1.0
     *
     * @param string $name Annotation name
     * @return array Array of annotations matching $name, or an associative array of all annotations
     */
    public function getAnnotations(string $name = null) : array
    {
        return $this->context->getAnnotations($this->path, $name);
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
        return $this->isRoot() ? null : end($this->path);
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
        if ($this->isRoot()) {
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
        return $this->getNodeAt('#/');
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
     * @param bool $onlyOne Get only the most specific type, rather than all applicable
     * @return int Bitmask list of applicable types (See JsonBrowser::TYPE_* constants)
     */
    public function getType(bool $onlyOne = false) : int
    {
        $type = Util::typeMask($this->getValue(), $onlyOne);
        if (!$this->currentlyExists) {
            if ($onlyOne) {
                $type = self::TYPE_UNDEFINED;
            } else {
                $type |= self::TYPE_UNDEFINED;
            }
        }

        return $type;
    }

    /**
     * Get the node value
     *
     * @since 1.0.0
     *
     * @param int    $asType Ensure that the returned value matches one of the specified types
     * @param bool   $cast   Whether to cast the value if necessary to ensure the correct type
     * @return mixed Node value
     */
    public function getValue(int $asType = 0, $cast = null)
    {
        $value = $this->context->getValue($this->path, $this->currentlyExists);
        
        if (($this->options & self::OPT_NONEXISTENT_EXCEPTIONS) && !$this->currentlyExists) {
            throw new Exception(self::ERR_UNKNOWN_SELF, 'Current node is unknown: %s', $this->getPath());
        }

        // ensure value is of the specified type
        if ($asType) {
            return $this->assertType($asType, $value, $cast);
        }

        return $value;
    }

    /**
     * Get the value at a given path
     *
     * @since 1.1.0
     *
     * @param string $path   JSON pointer to the requested node
     * @param int    $asType Ensure that the returned value matches one of the specified types
     * @param bool   $cast   Whether to cast the value if necessary to ensure the correct type
     * @return mixed Value at the given path
     */
    public function getValueAt(string $path, int $asType = 0, $cast = null)
    {
        $value = $this->context->getValue(Util::decodePointer($path), $this->currentlyExists);
        
        if (($this->options & self::OPT_NONEXISTENT_EXCEPTIONS) && !$this->currentlyExists) {
            throw new Exception(self::ERR_UNKNOWN_TARGET, 'Target node is unknown: %s', $path);
        }

        // ensure value is of the specified type
        if ($asType) {
            return $this->assertType($asType, $value, $cast);
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
     * Test whether the current node is the document root
     *
     * @since 2.3.0
     *
     * @return bool Whether this node is the document root
     */
    public function isRoot() : bool
    {
        return !count($this->path);
    }

    /**
     * Test whether the document value is of a given type
     *
     * @since 1.3.0
     *
     * @param int $types Types to test for
     * @param int $all   Whether to require all types, or just one
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
     * Load document from a JSON string
     *
     * @since 2.0.0
     *
     * @param string $json JSON-encoded document
     */
    public function loadJSON(string $json)
    {
        $document = null;

        // decode document
        Exception::wrap(function () use ($json, &$document) {
            try {
                // decode via json_decode for speed
                $document = json_decode($json);
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

        $this->attach($document);
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
     * Set a node annotation
     *
     * @since 2.1.0
     *
     * @param string $name  Annotation name
     * @param mixed  $value Annotation value
     * @param bool   $clear Clear existing annotations with the same name
     */
    public function setAnnotation(string $name, $value, bool $clear = false)
    {
        $this->context->setAnnotation($this->path, $name, $value, $clear);
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
        if ($this->isRoot()) {
            return false;
        }

        return $this->getParent()->childExists($key);
    }
}
