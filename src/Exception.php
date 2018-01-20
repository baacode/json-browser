<?php

namespace JsonBrowser;

/**
 * Custom exception class
 *
 * @since 1.0.0
 *
 * @package baacode/json-browser
 * @copyright (c) 2017-2018 Erayd LTD
 * @author Steve Gilberd <steve@erayd.net>
 * @license ISC
 */
class Exception extends \Exception
{
    /**
     * Create a new instance
     *
     * @since 1.0.0
     *
     * @param int $code Error code
     * @param string $message Error message
     * @param array $params Error params
     */
    public function __construct(int $code, string $message, ...$params)
    {
        // get previous exception
        if (count($params) && end($params) instanceof \Throwable) {
            $previous = array_pop($params);
        } else {
            $previous = null;
        }

        // format message
        if (count($params)) {
            $message = sprintf($message, ...$params);
        }

        // create parent
        parent::__construct($message, $code, $previous);
    }

    /**
     * Wrap some code and catch errors with a custom exception
     *
     * @since 1.0.0
     *
     * @param callable $target Function to wrap and execute
     * @param int $code Error code
     * @param string $message Error message
     * @param array $params Error params
     */
    public static function wrap(callable $target, int $code, string $message, ...$params)
    {
        try {
            set_error_handler(function (int $errno, string $message) {
                throw new \Exception($message, $errno);
            });
            return $target();
        } catch (\Throwable $e) {
            $params[] = $e->getMessage();
            $params[] = $e;
            throw new self($code, $message, ...$params);
        } finally {
            restore_error_handler();
        }
    }
}
