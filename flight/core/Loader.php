<?php
/**
 * Flight: An extensible micro-framework.
 *
 * @copyright   Copyright (c) 2011, Mike Cao <mike@mikecao.com>
 * @license     http://www.opensource.org/licenses/mit-license.php
 */

namespace flight\core;

/**
 * The Loader class is responsible for loading objects. It maintains
 * a list of reusable class instances and can generate a new class
 * instances with custom initialization parameters. It also performs
 * class autoloading.
 */
class Loader {
    /**
     * Registered classes.
     *
     * @var array
     */
    protected $classes = array();

    /**
     * Class instances.
     *
     * @var array
     */
    protected $instances = array();

    /**
     * Autoload directories.
     *
     * @var array
     */
    protected $dirs = array();

    /**
     * Registers a class.
     *
     * @param string $name Registry name
     * @param string $class Class name
     * @param array $params Class initialization parameters
     * @param callback $callback Function to call after object instantiation
     */
    public function register($name, $class, array $params = array(), $callback = null) {
        unset($this->instances[$class]);

        $this->classes[$name] = array($class, $params, $callback);
    }

    /**
     * Unregisters a class.
     *
     * @param string $name Registry name
     */
    public function unregister($name) {
        unset($this->classes[$name]);
    }

    /**
     * Loads a registered class.
     *
     * @param string $name Method name
     * @param bool $shared Shared instance
     * @return object Class instance
     */
    public function load($name, $shared = true) {
        if (isset($this->classes[$name])) {
            list($class, $params, $callback) = $this->classes[$name];

            $do_callback = ($callback && (!$shared || !isset($this->instances[$class])));

            $obj = ($shared) ?
                $this->getInstance($class, $params) :
                $this->newInstance($class, $params);

            if ($do_callback) {
                $ref = array(&$obj);
                call_user_func_array($callback, $ref);
            }

            return $obj;
        }

        return ($shared) ?
            $this->getInstance($name) :
            $this->newInstance($name);
    }

    /**
     * Gets a single instance of a class.
     *
     * @param string $class Class name
     * @param array $params Class initialization parameters
     */
    public function getInstance($class, array $params = array()) {
        if (!isset($this->instances[$class])) {
            $this->instances[$class] = $this->newInstance($class, $params);
        }

        return $this->instances[$class];
    }

    /**
     * Gets a new instance of a class.
     *
     * @param string $class Class name
     * @param array $params Class initialization parameters
     * @return object Class instance
     */
    public function newInstance($class, array $params = array()) {
        switch (count($params)) {
            case 0:
                return new $class();
            case 1:
                return new $class($params[0]);
            case 2:
                return new $class($params[0], $params[1]);
            case 3:
                return new $class($params[0], $params[1], $params[2]);
            case 4:
                return new $class($params[0], $params[1], $params[2], $params[3]);
            case 5:
                return new $class($params[0], $params[1], $params[2], $params[3], $params[4]);
            default:
                $refClass = new \ReflectionClass($class);
                return $refClass->newInstanceArgs($params);
        }
    }

    /**
     * Adds a directory for autoloading classes.
     *
     * @param mixed $dir Directory path
     */
    public function addDirectory($dir) {
        if (is_array($dir) || is_object($dir)) {
            foreach ($dir as $value) {
                $this->dirs[] = $value;
            }
        }
        else if (is_string($dir)) {
            $this->dirs[] = $dir;
        }
    }

    /**
     * Starts autoloader.
     */
    public function start() {
        spl_autoload_register(array($this, 'autoload'));
    }

    /**
     * Stops autoloading.
     */
    public function stop() {
        spl_autoload_unregister(array($this, 'autoload'));
    }

    /**
     * Autoloads classes.
     *
     * @param string $class Class name
     * @throws \Exception If class not found
     */
    public function autoload($class) {
        $class_file = str_replace('\\', '/', str_replace('_', '/', $class)).'.php';

        foreach ($this->dirs as $dir) {
            $file = $dir.'/'.$class_file;
            if (file_exists($file)) {
                require $file;
                return;
            }
        }

        // Allow other autoloaders to run before raising an error
        $loaders = spl_autoload_functions();
        $loader = array_pop($loaders);
        if (is_array($loader) && $loader[0] == __CLASS__ && $loader[1] == __FUNCTION__) {
            throw new \Exception('Unable to load file: '.$class_file);
        }
    }

    /**
     * Resets the object to the initial state.
     */
    public function reset() {
        $this->classes = array();
        $this->instances = array();
        $this->dirs = array();
    }
}
?>