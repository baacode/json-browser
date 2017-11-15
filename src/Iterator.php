<?php

namespace JsonBrowser;

/**
 * Iterate through child nodes
 *
 * @since 1.3.0
 *
 * @package baacode/json-browser
 * @copyright (c) 2017 Erayd LTD
 * @author Steve Gilberd <steve@erayd.net>
 * @license ISC
 */
class Iterator implements \Iterator
{
    /** Chained iterator */
    private $iterator = null;

    /** Browser instance */
    private $browser = null;

    /**
     * Create a new instance
     *
     * @since 1.3.0
     *
     * @param JsonBrowser $browser
     */
    public function __construct(JsonBrowser $browser)
    {
        $this->browser = $browser;

        $type = $browser->getType();
        if ($type & (JsonBrowser::TYPE_ARRAY | JsonBrowser::TYPE_OBJECT)) {
            $this->iterator = new \ArrayIterator($browser->getValue(), 0);
        } else {
            $this->iterator = new \EmptyIterator();
        }
    }

    /**
     * Get a browser object for the current child
     *
     * @since 1.3.0
     *
     * @return JsonBrowser
     */
    public function current() : JsonBrowser
    {
        return $this->browser->getChild($this->iterator->key());
    }

    /**
     * Get the current child index
     *
     * @since 1.3.0
     *
     * @return mixed
     */
    public function key()
    {
        return $this->iterator->key();
    }

    /**
     * Advance the internal pointer to the next child
     *
     * @since 1.3.0
     *
     */
    public function next()
    {
        return $this->iterator->next();
    }

    /**
     * Reset the internal pointer to the first child
     *
     * @since 1.3.0
     *
     */
    public function rewind()
    {
        return $this->iterator->rewind();
    }

    /**
     * Test whether there are more children to iterate over
     *
     * @since 1.3.0
     *
     * @return bool
     */
    public function valid() : bool
    {
        return $this->iterator->valid();
    }
}
