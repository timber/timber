<?php

namespace Timber;

use Exception;
use Stringable;

/**
 * Class FunctionWrapper
 *
 * With Timber, we want to prepare all the data before we echo content through a render function.
 * Some functionality in WordPress directly echoes output instead of returning it. This class makes
 * it easier to store the results of an echoing function by using ob_start() and ob_end_clean()
 * behind the scenes.
 */
class FunctionWrapper implements Stringable
{
    private $_class;

    private $_function;

    public function __toString()
    {
        try {
            return (string) $this->call();
        } catch (Exception $e) {
            return 'Caught exception: ' . $e->getMessage() . "\n";
        }
    }

    /**
     *
     *
     * @param callable $function
     * @param array $args
     * @param bool $return_output_buffer
     */
    public function __construct(
        $function,
        private $args = [],
        private $return_output_buffer = false
    ) {
        if (\is_array($function)) {
            if ((\is_string($function[0]) && \class_exists($function[0])) || \gettype($function[0]) === 'object') {
                $this->_class = $function[0];
            }

            if (\is_string($function[1])) {
                $this->_function = $function[1];
            }
        } else {
            $this->_function = $function;
        }
    }

    /**
     *
     *
     * @return string
     */
    public function call()
    {
        $args = $this->_parse_args(\func_get_args(), $this->args);
        $callable = (isset($this->_class)) ? [$this->_class, $this->_function] : $this->_function;

        if ($this->return_output_buffer) {
            return Helper::ob_function($callable, $args);
        } else {
            return \call_user_func_array($callable, $args);
        }
    }

    /**
     *
     *
     * @param array   $args
     * @param array   $defaults
     * @return array
     */
    private function _parse_args($args, $defaults)
    {
        $_arg = \reset($defaults);

        foreach ($args as $index => $arg) {
            $defaults[$index] = \is_null($arg) ? $_arg : $arg;
            $_arg = \next($defaults);
        }

        return $defaults;
    }
}
