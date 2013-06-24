<?php
/**
 * Flight: An extensible micro-framework.
 *
 * @copyright   Copyright (c) 2011, Mike Cao <mike@mikecao.com>
 * @license     http://www.opensource.org/licenses/mit-license.php
 */

include __DIR__.'/core/Loader.php';
include __DIR__.'/core/Dispatcher.php';

/**
 * The Flight class represents the framework itself. It is responsible
 * loading an HTTP request, running the assigned services, and generating
 * an HTTP response. 
 */
class Flight {
    /**
     * Stored variables.
     *
     * @var array
     */
    protected static $vars = array();

    /**
     * Class loader.
     *
     * @var object
     */
    protected static $loader;

    /**
     * Event dispatcher.
     *
     * @var object
     */
    protected static $dispatcher;

    // Don't allow object instantiation
    private function __construct() {}
    private function __destruct() {}
    private function __clone() {}

    /**
     * Handles calls to static methods.
     *
     * @param string $name Method name
     * @param array $params Method parameters
     */
    public static function __callStatic($name, $params) {
        $callback = self::$dispatcher->get($name);

        if (is_callable($callback)) {
            return self::$dispatcher->run($name, $params);
        }

        $shared = (!empty($params)) ? (bool)$params[0] : true; 

        return self::$loader->load($name, $shared);
    }

    /*** Core Methods ***/

    /**
     * Initializes the framework.
     */
    public static function init() {
        // Handle errors internally
        set_error_handler(array(__CLASS__, 'handleError'));

        // Handle exceptions internally
        set_exception_handler(array(__CLASS__, 'handleException'));

        // Load core components
        if (self::$loader == null) {
            self::$loader = new \flight\core\Loader();
            self::$loader->start();
        }
        else {
            self::$loader->reset();
        }

        if (self::$dispatcher == null) {
            self::$dispatcher = new \flight\core\Dispatcher();
        }
        else {
            self::$dispatcher->reset();
        }

        // Register framework directory
        self::$loader->addDirectory(dirname(__DIR__));

        // Register default components
        self::$loader->register('request', '\flight\net\Request');
        self::$loader->register('response', '\flight\net\Response');
        self::$loader->register('router', '\flight\net\Router');
        self::$loader->register('view', '\flight\template\View', array(), function($view){
            $view->path = Flight::get('flight.views.path');
        });

        // Register framework methods
        $methods = array(
            'start','stop','route','halt','error','notFound',
            'render','redirect','etag','lastModified','json'
        );
        foreach ($methods as $name) {
            self::$dispatcher->set($name, array(__CLASS__, '_'.$name));
        }

        // Default settings
        self::set('flight.views.path', './views');
        self::set('flight.log_errors', false);

        // Enable output buffering
        ob_start();
    }

    /**
     * Custom error handler. Converts errors into exceptions.
     *
     * @param int $errno Error number
     * @param int $errstr Error string
     * @param int $errfile Error file name
     * @param int $errline Error file line number
     */
    public static function handleError($errno, $errstr, $errfile, $errline) {
        if ($errno & error_reporting()) {
            static::handleException(new ErrorException($errstr, $errno, 0, $errfile, $errline));
        }
    }

    /**
     * Custom exception handler. Logs exceptions.
     *
     * @param Exception $e Thrown exception
     */
    public static function handleException(Exception $e) {
        if (self::get('flight.log_errors')) {
            error_log($e->getMessage());
        }
        static::error($e);
    }

    /**
     * Maps a callback to a framework method.
     *
     * @param string $name Method name
     * @param callback $callback Callback function
     * @throws Exception If trying to map over a framework method
     */
    public static function map($name, $callback) {
        if (method_exists(__CLASS__, $name)) {
            throw new Exception('Cannot override an existing framework method.');
        }

        self::$dispatcher->set($name, $callback);
    }

    /**
     * Registers a class to a framework method.
     *
     * @param string $name Method name
     * @param string $class Class name
     * @param array $params Class initialization parameters
     * @param callback $callback Function to call after object instantiation
     * @throws Exception If trying to map over a framework method
     */
    public static function register($name, $class, array $params = array(), $callback = null) {
        if (method_exists(__CLASS__, $name)) {
            throw new Exception('Cannot override an existing framework method.');
        }

        self::$loader->register($name, $class, $params, $callback);
    }

    /**
     * Adds a pre-filter to a method.
     *
     * @param string $name Method name
     * @param callback $callback Callback function
     */
    public static function before($name, $callback) {
        self::$dispatcher->hook($name, 'before', $callback);
    }

    /**
     * Adds a post-filter to a method.
     *
     * @param string $name Method name
     * @param callback $callback Callback function
     */
    public static function after($name, $callback) {
        self::$dispatcher->hook($name, 'after', $callback);
    }

    /**
     * Gets a variable.
     *
     * @param string $key Key
     * @return mixed
     */
    public static function get($key) {
        return isset(self::$vars[$key]) ? self::$vars[$key] : null;
    }

    /**
     * Sets a variable.
     *
     * @param mixed $key Key
     * @param string $value Value
     */
    public static function set($key, $value = null) {
        if (is_array($key) || is_object($key)) {
            foreach ($key as $k => $v) {
                self::$vars[$k] = $v;
            }
        }
        else {
            self::$vars[$key] = $value;
        }
    }

    /**
     * Checks if a variable has been set.
     *
     * @param string $key Key
     * @return bool Variable status
     */
    public static function has($key) {
        return isset(self::$vars[$key]);
    }

    /**
     * Unsets a variable. If no key is passed in, clear all variables.
     *
     * @param string $key Key
     */
    public static function clear($key = null) {
        if (is_null($key)) {
            self::$vars = array();
        }
        else {
            unset(self::$vars[$key]);
        }
    }

    /**
     * Adds a path for class autoloading.
     *
     * @param string $dir Directory path
     */
    public static function path($dir) {
        self::$loader->addDirectory($dir);
    }

    /*** Extensible Methods ***/

    /**
     * Starts the framework.
     */
    public static function _start() {
        $dispatched = false;

        // Route the request
        while ($route = self::router()->route(self::request())) {
            $params = array_values($route->params);
            $continue = self::$dispatcher->execute(
                $route->callback,
                $params
            );
            $dispatched = true;

            if ($continue) {
                self::router()->next();
            }
            else {
                break;
            }
        }

        if (!$dispatched) {
            self::notFound();
        }

        // Disable caching for AJAX requests
        if (self::request()->ajax) {
            self::response()->cache(false);
        }

        // Allow post-filters to run
        self::after('start', array(__CLASS__, 'stop'));
    }

    /**
     * Stops the framework and outputs the current response.
     */
    public static function _stop() {
        self::response()
            ->write(ob_get_clean())
            ->send();
    }

    /**
     * Stops processing and returns a given response.
     *
     * @param int $code HTTP status code
     * @param string $message Response message
     */
    public static function _halt($code = 200, $message = '') {
        self::response(false)
            ->status($code)
            ->write($message)
            ->cache(false)
            ->send();
    }

    /**
     * Sends an HTTP 500 response for any errors.
     *
     * @param \Exception Thrown exception
     */
    public static function _error(Exception $e) {
        $msg = sprintf('<h1>500 Internal Server Error</h1>'.
            '<h3>%s (%s)</h3>'.
            '<pre>%s</pre>',
            $e->getMessage(),
            $e->getCode(),
            $e->getTraceAsString()
        ); 

        try {
            self::response(false)
                ->status(500)
                ->write($msg)
                ->send();
        }
        catch (Exception $ex) {
            exit($msg);
        }
    }

    /**
     * Sends an HTTP 404 response when a URL is not found.
     */
    public static function _notFound() {
        self::response(false)
            ->status(404)
            ->write(
                '<h1>404 Not Found</h1>'.
                '<h3>The page you have requested could not be found.</h3>'.
                str_repeat(' ', 512)
            )
            ->send();
    }

    /**
     * Routes a URL to a callback function.
     *
     * @param string $pattern URL pattern to match
     * @param callback $callback Callback function
     */
    public static function _route($pattern, $callback) {
        self::router()->map($pattern, $callback);
    }

    /**
     * Redirects the current request to another URL.
     *
     * @param string $url URL
     * @param int $code HTTP status code
     */
    public static function _redirect($url, $code = 303) {
        $base = self::request()->base;
        if ($base != '/' && strpos($url, '://') === false) {
            $url = $base.(($url[0] == '/') ? '' : '/').$url;
        }

        self::response(false)
            ->status($code)
            ->header('Location', $url)
            ->write($url)
            ->send();
    }

    /**
     * Renders a template.
     *
     * @param string $file Template file
     * @param array $data Template data
     * @param string $key View variable name
     */
    public static function _render($file, $data = null, $key = null) {
        if ($key !== null) {
            self::view()->set($key, self::view()->fetch($file, $data));
        }
        else {
            self::view()->render($file, $data);
        }
    }

    /**
     * Sends a JSON response.
     *
     * @param mixed $data Data to JSON encode
     */
    public static function _json($data) {
        self::response()
            ->status(200)
            ->header('Content-Type', 'application/json')
            ->write(json_encode($data))
            ->send();
    }

    /**
     * Handles ETag HTTP caching.
     *
     * @param string $id ETag identifier
     * @param string $type ETag type
     */
    public static function _etag($id, $type = 'strong') {
        $id = (($type === 'weak') ? 'W/' : '').$id;

        self::response()->header('ETag', $id);
        
        if ($id === getenv('HTTP_IF_NONE_MATCH')) {
            self::halt(304);
        }
    }

    /**
     * Handles last modified HTTP caching.
     *
     * @param int $time Unix timestamp
     */
    public static function _lastModified($time) {
        self::response()->header('Last-Modified', date(DATE_RFC1123, $time));

        if ($time === strtotime(getenv('HTTP_IF_MODIFIED_SINCE'))) {
            self::halt(304);
        }
    }
}

// Initialize the framework on include
Flight::init();
?>
