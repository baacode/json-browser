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
class JsonBrowser
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
     * Get the JSON source for the current node
     *
     * @since 1.2.0
     *
     * @param int $options Bitwise options for json_encode()
     * @return string Encoded JSON string
     */
    public function getJSON(int $options = \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE)
    {
        return json_encode($this->document, $options);
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
        if (is_null($this->document)) {
            return self::TYPE_NULL;
        }

        if (is_bool($this->document)) {
            return self::TYPE_BOOLEAN;
        }

        if (is_string($this->document)) {
            return self::TYPE_STRING;
        }

        if (is_numeric($this->document)) {
            $type = self::TYPE_NUMBER;
            if (is_int($this->document) || $this->document == floor($this->document)) {
                $type |= self::TYPE_INTEGER;
            }
            return $type;
        }

        if (is_array($this->document)) {
            return self::TYPE_ARRAY;
        }

        if (is_object($this->document)) {
            return self::TYPE_OBJECT;
        }

        throw new Exception(self::ERR_UNKNOWN_TYPE, 'Unknown type: %s', gettype($this->document)); // @codeCoverageIgnore
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
     * Check whether a child element exists
     *
     * @since 1.0.0
     *
     * @param mixed $key Index key
     * @return bool Whether the given child exists
     */
    public function childExists($key) : bool
    {
        if (is_array($this->document)) {
            return array_key_exists($key, $this->document);
        } elseif (is_object($this->document)) {
            return property_exists($this->document, $key);
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
        if (!$this->childExists($key)) {
            if ($this->options & self::OPT_NONEXISTENT_EXCEPTIONS) {
                throw new Exception(self::ERR_UNKNOWN_CHILD, 'Unknown child: %s', $key);
            }
            $child = clone $this;
            $child->document = null;
        } else {
            $child = clone $this;
            if (is_array($this->document)) {
                $child->document = $this->document[$key];
            } elseif (is_object($this->document)) {
                $child->document = $this->document->$key;
            }
        }

        $child->parent = $this;
        $child->path[] = strtr($key, ['~' => '~0', '/' => '~1', '%' => '%25']);

        return $child;
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
     * Get the node at a given path
     *
     * @since 1.0.0
     *
     * @param string $path JSON pointer to the requested node
     * @return self
     */
    public function getNodeAt(string $path) : self
    {
        $path = array_map(function ($element) {
            return strtr($element, ['%25' => '%', '~1' => '/', '~0' => '~']);
        }, explode('/', substr($path, 2)));

        $node = $this->root;
        while (count($path)) {
            $node = $node->getChild(array_shift($path));
        }

        return $node;
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
}
