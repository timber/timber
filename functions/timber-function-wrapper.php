<?php

class TimberFunctionWrapper
{

    private $_function;
    private $_args;
    private $_use_ob;

    public function __toString() {
        return $this->call();
    }

    /**
     * @param callable $function
     * @param array $args
     * @param bool $return_output_buffer
     */
    public function __construct($function, $args = array(), $return_output_buffer = false) {
        $this->_function = $function;
        $this->_args = $args;
        $this->_use_ob = $return_output_buffer;

        add_filter('get_twig', array(&$this, 'add_to_twig'));
    }

    /**
     * @param Twig_Environment $twig
     * @return Twig_Environment
     */
    public function add_to_twig($twig) {
        $wrapper = $this;

        $twig->addFunction(new Twig_SimpleFunction($this->_function, function () use ($wrapper) {
            return call_user_func_array(array($wrapper, 'call'), func_get_args());
        }));

        return $twig;
    }

    /**
     * @return string
     */
    public function call() {
        $args = $this->_parse_args(func_get_args(), $this->_args);

        if ($this->_use_ob) {
            return TimberHelper::ob_function($this->_function, $args);
        } else {
            return (string)call_user_func_array($this->_function, $args);
        }
    }

    /**
     * @param array $args
     * @param array $defaults
     * @return array
     */
    private function _parse_args($args, $defaults) {
        $_arg = reset($defaults);

        foreach ($args as $index => $arg) {
            $defaults[$index] = is_null($arg) ? $_arg : $arg;
            $_arg = next($defaults);
        }

        return $defaults;
    }

}