<?php

namespace Timber;

use Timber\Twig;
use Timber\ImageHelper;
use Timber\Admin;
use Timber\Integrations;
use Timber\PostGetter;
use Timber\TermGetter;
use Timber\Site;
use Timber\URLHelper;
use Timber\Helper;
use Timber\Pagination;
use Timber\Request;
use Timber\User;
use Timber\Loader;

/**
 * Timber Class.
 *
 * Main class called Timber for this plugin.
 *
 * @example
 * ```php
 *  $posts = Timber::get_posts();
 *  $posts = Timber::get_posts('post_type = article')
 *  $posts = Timber::get_posts(array('post_type' => 'article', 'category_name' => 'sports')); // uses wp_query format.
 *  $posts = Timber::get_posts(array(23,24,35,67), 'InkwellArticle');
 *
 *  $context = Timber::get_context(); // returns wp favorites!
 *  $context['posts'] = $posts;
 *  Timber::render('index.twig', $context);
 * ```
 */
class Timber {

	public static $version = '1.2.4';
	public static $locations;
	public static $dirname = 'views';
	public static $twig_cache = false;
	public static $cache = false;
	public static $auto_meta = true;
	public static $autoescape = false;

	public static $context_cache = array();

	private static $twigEnvironment;
	private static $twigEnvironmentOptions = array();
	
	private static $twigLoaderClassname = __NAMESPACE__.'\LegacyLoader';
	private static $twigEnvironmentClassname = '\Twig_Environment';

	/**
	 * @codeCoverageIgnore
	 */
	public function __construct(array $options = null)
	{	
		if (defined('TIMBER_LOADED')) {

			if ($options !== null) {
				throw new \LogicException('Creation with $options prohibited, since Timber has already been configured by an other instance.');
			}
			
		} else {

			$options = is_array($options) ? $options : array();
			
			static::init($options);

			if (isset($options['experimental:loader']) && is_string($options['experimental:loader'])) {
				switch ($option = $options['experimental:loader']) {

					case 'legacy':
						self::$twigLoaderClassname = __NAMESPACE__.'\LegacyLoader';
						break;

					case 'compatible':
						self::$twigLoaderClassname = __NAMESPACE__.'\CompatibleLoader';
						break;

					default:
						throw new \Exception("Configuration error: '${option}' is not a valid loader mode.");
				}
			}

			if (isset($options['experimental:reuse_environment']) && $options['experimental:reuse_environment'] === true) {
				$loader = self::createTwigLoader();
				static::$twigEnvironment = static::createTwigEnvironment($loader , array());
			}
		}
	}

	/**
	 * Tests whether we can use Timber
	 * @codeCoverageIgnore
	 * @return
	 */
	private static function test_compatibility() {
		if ( is_admin() || $_SERVER['PHP_SELF'] == '/wp-login.php' ) {
			return;
		}
		if ( version_compare(phpversion(), '5.3.0', '<') && !is_admin() ) {
			trigger_error('Timber requires PHP 5.3.0 or greater. You have '.phpversion(), E_USER_ERROR);
		}
		if ( !class_exists('Twig_Token') ) {
			trigger_error('You have not run "composer install" to download required dependencies for Timber, you can read more on https://github.com/timber/timber#installation', E_USER_ERROR);
		}
	}

	/**
	 * @codeCoverageIgnore
	 */
	private static function backwards_compatibility() {
		if ( class_exists('TimberArchives') ) {
			//already run, so bail
			return;
		}
		$names = array('Archives', 'Comment', 'Core', 'FunctionWrapper', 'Helper', 'Image', 'ImageHelper', 'Integrations', 'Loader', 'Menu', 'MenuItem', 'Post', 'PostGetter', 'PostCollection', 'QueryIterator', 'Request', 'Site', 'Term', 'TermGetter', 'Theme', 'Twig', 'URLHelper', 'User', 'Integrations\Command', 'Integrations\ACF', 'Cache');
		foreach ( $names as $name ) {
			$old_class_name = 'Timber'.str_replace('Integrations\\', '', $name);
			$new_class_name = 'Timber\\'.$name;
			if ( class_exists($new_class_name) ) {
				class_alias($new_class_name, $old_class_name);
			}
		}
		class_alias(__CLASS__, 'Timber');
		if ( class_exists('Timber\\'.'Integrations\Timber_WP_CLI_Command') ) {
			class_alias('Timber\\'.'Integrations\Timber_WP_CLI_Command', 'Timber_WP_CLI_Command');
		}
	}

	private static function init_constants() {
		defined("TIMBER_LOC") or define("TIMBER_LOC", realpath(dirname(__DIR__)));
	}

	/**
	 * @codeCoverageIgnore
	 */
	protected static function init() {
		if ( !defined('ABSPATH') ) {
			trigger_error('Timber requires Wordpress to be loaded!', E_USER_ERROR);
		}
		if ( class_exists('\WP') && !defined('TIMBER_LOADED') ) {
			static::test_compatibility();
			static::backwards_compatibility();
			static::init_constants();
			Twig::init();
			ImageHelper::init();
			Admin::init();
			new Integrations();
			define('TIMBER_LOADED', true);
		}
	}

	/* Post Retrieval Routine
	================================ */

	/**
	 * Get a post by post ID or query (as a query string or an array of arguments).
	 * But it's also cool
	 *
	 * @api
	 * @param mixed        $query     Optional. Post ID or query (as query string or an array of arguments for
	 *                                WP_Query). If a query is provided, only the first post of the result will be
	 *                                returned. Default false.
	 * @param string|array $PostClass Optional. Class to use to wrap the returned post object. Default 'Timber\Post'.
	 * @return \Timber\Post|bool Timber\Post object if a post was found, false if no post was found.
	 */
	public static function get_post( $query = false, $PostClass = 'Timber\Post' ) {
		return PostGetter::get_post($query, $PostClass);
	}

	/**
	 * Get posts.
	 * @api
	 * @example
	 * ```php
	 * $posts = Timber::get_posts();
 	 *  $posts = Timber::get_posts('post_type = article')
 	 *  $posts = Timber::get_posts(array('post_type' => 'article', 'category_name' => 'sports')); // uses wp_query format.
 	 *  $posts = Timber::get_posts('post_type=any', array('portfolio' => 'MyPortfolioClass', 'alert' => 'MyAlertClass')); //use a classmap for the $PostClass
	 * ```
	 * @param mixed   $query
	 * @param string|array  $PostClass
	 * @return array|bool|null
	 */
	public static function get_posts( $query = false, $PostClass = 'Timber\Post', $return_collection = false ) {
		return PostGetter::get_posts($query, $PostClass, $return_collection);
	}

	/**
	 * Query post.
	 * @api
	 * @param mixed   $query
	 * @param string  $PostClass
	 * @return array|bool|null
	 */
	public static function query_post( $query = false, $PostClass = 'Timber\Post' ) {
		return PostGetter::query_post($query, $PostClass);
	}

	/**
	 * Query posts.
	 * @api
	 * @param mixed   $query
	 * @param string  $PostClass
	 * @return PostCollection
	 */
	public static function query_posts( $query = false, $PostClass = 'Timber\Post' ) {
		return PostGetter::query_posts($query, $PostClass);
	}

	/* Term Retrieval
	================================ */

	/**
	 * Get terms.
	 * @api
	 * @param string|array $args
	 * @param array   $maybe_args
	 * @param string  $TermClass
	 * @return mixed
	 */
	public static function get_terms( $args = null, $maybe_args = array(), $TermClass = 'Timber\Term' ) {
		return TermGetter::get_terms($args, $maybe_args, $TermClass);
	}

	/**
	 * Get term.
	 * @api
	 * @param int|WP_Term|object $term
	 * @param string     $taxonomy
	 * @return Timber\Term|WP_Error|null
	 */
	public static function get_term( $term, $taxonomy = 'post_tag', $TermClass = 'Timber\Term' ) {
		return TermGetter::get_term($term, $taxonomy, $TermClass);
	}

	/* Site Retrieval
	================================ */

	/**
	 * Get sites.
	 * @api
	 * @param array|bool $blog_ids
	 * @return array
	 */
	public static function get_sites( $blog_ids = false ) {
		if ( !is_array($blog_ids) ) {
			global $wpdb;
			$blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs ORDER BY blog_id ASC");
		}
		$return = array();
		foreach ( $blog_ids as $blog_id ) {
			$return[] = new Site($blog_id);
		}
		return $return;
	}


	/*  Template Setup and Display
	================================ */

	/**
	 * Get context.
	 * @api
	 * @return array
	 */
	public static function get_context() {
		if ( empty(self::$context_cache) ) {
			self::$context_cache['http_host'] = URLHelper::get_scheme().'://'.URLHelper::get_host();
			self::$context_cache['wp_title'] = Helper::get_wp_title();
			self::$context_cache['body_class'] = implode(' ', get_body_class());

			self::$context_cache['site'] = new Site();
			self::$context_cache['request'] = new Request();
			$user = new User();
			self::$context_cache['user'] = ($user->ID) ? $user : false;
			self::$context_cache['theme'] = self::$context_cache['site']->theme;

			self::$context_cache['posts'] = new PostQuery();

			/**
			 * @deprecated as of Timber 1.3.0
			 * @todo remove in Timber 1.4.*
			 */
			self::$context_cache['wp_head'] = new FunctionWrapper( 'wp_head' );
			self::$context_cache['wp_footer'] = new FunctionWrapper( 'wp_footer' );

			self::$context_cache = apply_filters('timber_context', self::$context_cache);
			self::$context_cache = apply_filters('timber/context', self::$context_cache);
		}


		return self::$context_cache;
	}

	/**
	 *  
	 * @return \Twig_LoaderInterface
	 */
	protected static function createTwigLoader()
	{
		return new self::$twigLoaderClassname();
	}

	/**
	 *  
	 * @return \Twig_Environment
	 */
	protected static function createTwigEnvironment(\Twig_LoaderInterface $loader, array $options = array())
	{
		$loader = apply_filters('timber/loader/loader', $loader);
// TODO: Consider this new filter as a future replacement for 'timber/loader/loader'
//		$loader = apply_filters('timber/twig/loader', $loader, $this);
		if ( !$loader instanceof \Twig_LoaderInterface ) {
			throw new \UnexpectedValueException('Loader must implement \Twig_LoaderInterface');
		}

		if ($loader instanceof LegacyLoader) {
			// It's a legacy loader...
		}

		$options = array('debug' => WP_DEBUG, 'autoescape' => false);
		if ( isset(Timber::$autoescape) ) {
			$options['autoescape'] = Timber::$autoescape;
		}

// TODO: Consider this new (experimental) filter!
//		$options = apply_filters('timber/twig/options', $options, $this);

// TODO: Move these if's to Timbers controller / init()
		if ( Timber::$cache === true ) {
			Timber::$twig_cache = true;
		}
		if ( Timber::$twig_cache ) {
			$twig_cache_loc = apply_filters('timber/cache/location', TIMBER_LOC.'/cache/twig');
			if ( !file_exists($twig_cache_loc) ) {
				mkdir($twig_cache_loc, 0777, true);
			}
			$options['cache'] = $twig_cache_loc;
		}

		$twigEnvironment = new self::$twigEnvironmentClassname($loader, $options);

		if ( WP_DEBUG ) {
			$twigEnvironment->addExtension(new \Twig_Extension_Debug());
		}

		$twigEnvironment->addExtension(
			self::createAsm89CacheExtension(
				Cache::getAdapter(Cache::CACHE_USE_DEFAULT, 'timber')
			)
		);

		do_action('timber/twig', $twigEnvironment);
		/**
		 * get_twig is deprecated, use timber/twig
		 */
		do_action('get_twig', $twigEnvironment);
		
		return $twigEnvironment;
	}

	/**
	 *  
	 * @return \Twig_Environment
	 */
	public static function getTwigEnvironment()
	{
		if (static::$twigEnvironment !== null) {
			return static::$twigEnvironment;
		} else {
			return static::createTwigEnvironment(self::createTwigLoader(), self::$twigEnvironmentOptions);
		}
	}

	/**
	 * Get first existing template.
	 *
	 * @param array|string $templates  Name(s) of the Twig template(s) to choose from.
	 * @return string|bool             Name of chosen template, otherwise false.
	 */
	private static function chooseTemplate(\Twig_LoaderInterface $loader, $templates ) {
		// Change $templates into array, if needed 
		if ( !is_array($templates) ) {
			$templates = (array) $templates;
		}
		
		// Run through template array
		foreach ( $templates as $template ) {
			// Use the Twig loader to test for existance
			if ( $loader->exists($template) ) {
				// Return name of existing template
				return $template;
			}
		}

		// No existing template was found
		return false;
	}

	/**
	 * @return \Asm89\Twig\CacheExtension\Extension
	 */
	protected static function createAsm89CacheExtension(\Psr\SimpleCache\CacheInterface $adapter)
	{
		$key_generator   = new \Timber\Cache\KeyGenerator();
		$cache_provider  = new \Timber\Cache\Psr16\Asm89SimpleCacheAdapter($adapter);
		$cache_strategy  = new \Asm89\Twig\CacheExtension\CacheStrategy\GenerationalCacheStrategy($cache_provider, $key_generator);
		$cache_extension = new \Asm89\Twig\CacheExtension\Extension($cache_strategy);

		return $cache_extension;
	}

	/**
	 * Compile a Twig file.
	 *
	 * Passes data to a Twig file and returns the output.
	 *
	 * @api
	 * @example
	 * ```php
	 * $data = array(
	 *     'firstname' => 'Jane',
	 *     'lastname' => 'Doe',
	 *     'email' => 'jane.doe@example.org',
	 * );
	 *
	 * $team_member = Timber::compile( 'team-member.twig', $data );
	 * ```
	 * @param array|string $names      Name of the Twig file to render. If this is an array of files, Timber will
	 *                                 render the first file that exists.
	 * @param array        $context    Optional. An array of data to use in Twig template.
	 * @param bool|int     $expires    Optional. In seconds. Use false to disable cache altogether. When passed an
	 *                                 array, the first value is used for non-logged in visitors, the second for users.
	 *                                 Default false.
	 * @param string       $cache_mode Optional. Any of the cache mode constants defined in TimberLoader.
	 * @param bool         $via_render Optional. Whether to apply optional render or compile filters. Default false.
	 * @return bool|string The returned output.
	 */
	public static function compile( $names, $context = array(), $expires = false, $cache_mode = Cache::CACHE_USE_DEFAULT, $via_render = false ) {
		if ( !defined('TIMBER_LOADED') ) {
			new self();
		}
		
		$twigEnvironment = static::getTwigEnvironment();

		$loader = $twigEnvironment->getLoader();
		
		$supportCaller = $loader instanceof CallerCompatibleLoaderInterface;
		if ($supportCaller) {
			$callerDir = LocationManager::get_calling_script_dir(1);
			$loader->setCaller($callerDir);
		}

		$name = self::chooseTemplate($loader, $names);

		$callerFile = LocationManager::get_calling_script_file(1);
		do_action('timber/calling_php_file', $callerFile);

		$name = apply_filters($via_render ? 'timber_render_file' : 'timber_compile_file', $name);

		$output = false;

		if ($name !== false) {
			if ( is_null($context) ) {
				$context = array();
			}

			$context = apply_filters($via_render ? 'timber_render_data' : 'timber_compile_data', $context);

//
// Content from moved Loader::render() begins here.
//
			// Different $expires if user is anonymous or logged in
			if ( is_array($expires) ) {
				/** @var array $expires */
				if ( is_user_logged_in() && isset($expires[1]) ) {
					$expires = $expires[1];
				} else {
					$expires = $expires[0];
				}
			}

			// Define variables used below
			$key = null;
			$output = false;

			// Only load cached data when $expires is not false
			// NB: Caching is disabled, when $expires is false!
			if ( false !== $expires ) {

				// Sort array by key (to make md5() generate same result on identical context)
				if (ksort($context) === false ) {
					// TODO: Handle error...
				}

				// Generate cache key, by generating a md5 hash of the template name joined with a json version of the array (serializing via json is apparently faster)
				$key = md5($name.json_encode($context));

				// Load cached output
				$output = Cache::fetch($key, $cache_mode);
			}

			// If no output at this point, generate some...
			if ( false === $output || null === $output ) {

				// Only call this action, if the length of the template name is longer than 0 chars
				// TODO: Consider if this ever evaluates to false.
				if ( strlen($name) ) {
					// Get twig loader
					$loader = $twigEnvironment->getLoader();
					// Get loaders cache key.
					$result = $loader->getCacheKey($name);
					// Call action, exposing the loaders cache key
					do_action('timber_loader_render_file', $result);
				}

				// Create Twig_Template object
				$template = $twigEnvironment->loadTemplate($name);

				// Filter context data
				$context = apply_filters('timber_loader_render_data', $context);
				$context = apply_filters('timber/loader/render_data', $context, $name);

				// Render template
				$output = $template->render($context);
			}

			// Update cache, when 3) $key has been ser, 2) $expires != false, and 1) $output has ben changed from the initial false
			if ( false !== $output && false !== $expires && null !== $key ) {
				// Erase cache
				Cache::deleteCache();
				// Store output
				Cache::save($key, $output, $expires, $cache_mode);
			}
//
// Content from moved Loader::render() ends here.
//

			// Filter output
			$output = apply_filters('timber_output', $output);
			$output = apply_filters('timber/output', $output, $context, $name);
		}
		
		if ($supportCaller) {
			$loader->resetCaller();
		}

		do_action('timber_compile_done');
		return $output;
	}

	/**
	 * Compile a string.
	 *
	 * @api
	 * @example
	 * ```php
	 * $data = array(
	 *     'username' => 'Jane Doe',
	 * );
	 *
	 * $welcome = Timber::compile_string( 'Hi {{ username }}, I’m a string with a custom Twig variable', $data );
	 * ```
	 * @param string $string A string with Twig variables.
	 * @param array  $context Optional. An array of data to use in Twig template.
	 * @return  bool|string
	 */
	public static function compile_string( $string, $context = array() ) {
		$twigEnvironment = static::getTwigEnvironment();
		$template = $twigEnvironment->createTemplate($string);
		return $template->render($context);
	}

	/**
	 * Fetch function.
	 *
	 * @api
	 * @param array|string $names      Name of the Twig file to render. If this is an array of files, Timber will
	 *                                 render the first file that exists.
	 * @param array        $context    Optional. An array of data to use in Twig template.
	 * @param bool|int     $expires    Optional. In seconds. Use false to disable cache altogether. When passed an
	 *                                 array, the first value is used for non-logged in visitors, the second for users.
	 *                                 Default false.
	 * @param string       $cache_mode Optional. Any of the cache mode constants defined in TimberLoader.
	 * @return bool|string The returned output.
	 */
	public static function fetch( $names, $context = array(), $expires = false, $cache_mode = Cache::CACHE_USE_DEFAULT ) {
		$output = self::compile($names, $context, $expires, $cache_mode, true);
		$output = apply_filters('timber_compile_result', $output);
		return $output;
	}

	/**
	 * Render function.
	 *
	 * Passes data to a Twig file and echoes the output.
	 *
	 * @api
	 * @example
	 * ```php
	 * $context = Timber::get_context();
	 *
	 * Timber::render( 'index.twig', $context );
	 * ```
	 * @param array|string $names      Name of the Twig file to render. If this is an array of files, Timber will
	 *                                 render the first file that exists.
	 * @param array        $context    Optional. An array of data to use in Twig template.
	 * @param bool|int     $expires    Optional. In seconds. Use false to disable cache altogether. When passed an
	 *                                 array, the first value is used for non-logged in visitors, the second for users.
	 *                                 Default false.
	 * @param string       $cache_mode Optional. Any of the cache mode constants defined in TimberLoader.
	 * @return bool|string The echoed output.
	 */
	public static function render( $names, $context = array(), $expires = false, $cache_mode = Cache::CACHE_USE_DEFAULT ) {
		$output = self::fetch($names, $context, $expires, $cache_mode);
		echo $output;
		return $output;
	}

	/**
	 * Render a string with Twig variables.
	 *
	 * @api
	 * @example
	 * ```php
	 * $data = array(
	 *     'username' => 'Jane Doe',
	 * );
	 *
	 * Timber::render_string( 'Hi {{ username }}, I’m a string with a custom Twig variable', $data );
	 * ```
	 * @param string $string A string with Twig variables.
	 * @param array  $data   An array of data to use in Twig template.
	 * @return bool|string
	 */
	public static function render_string( $string, $data = array() ) {
		$compiled = self::compile_string($string, $data);
		echo $compiled;
		return $compiled;
	}


	/*  Sidebar
	================================ */

	/**
	 * Get sidebar.
	 * @api
	 * @param string  $sidebar
	 * @param array   $data
	 * @return bool|string
	 */
	public static function get_sidebar( $sidebar = 'sidebar.php', $data = array() ) {
		if ( strstr(strtolower($sidebar), '.php') ) {
			return self::get_sidebar_from_php($sidebar, $data);
		}
		return self::compile($sidebar, $data);
	}

	/**
	 * Get sidebar from PHP
	 * @api
	 * @param string  $sidebar
	 * @param array   $data
	 * @return string
	 */
	public static function get_sidebar_from_php( $sidebar = '', $data ) {
		$callerDir = LocationManager::get_calling_script_dir(1);
		$uris = LocationManager::get_locations($callerDir);
		ob_start();
		$found = false;
		foreach ( $uris as $uri ) {
			if ( file_exists(trailingslashit($uri).$sidebar) ) {
				include trailingslashit($uri).$sidebar;
				$found = true;
				break;
			}
		}
		if ( !$found ) {
			Helper::error_log('error loading your sidebar, check to make sure the file exists');
		}
		$ret = ob_get_contents();
		ob_end_clean();
		return $ret;
	}

	/* Widgets
	================================ */

	/**
	 * Get widgets.
	 *
	 * @api
	 * @param int|string $widget_id Optional. Index, name or ID of dynamic sidebar. Default 1.
	 * @return string
	 */
	public static function get_widgets( $widget_id ) {
		return trim( Helper::ob_function( 'dynamic_sidebar', array( $widget_id ) ) );
	}

	/*  Pagination
	================================ */

	/**
	 * Get pagination.
	 * @api
	 * @param array   $prefs
	 * @return array mixed
	 */
	public static function get_pagination( $prefs = array() ) {
		return Pagination::get_pagination($prefs);
	}

	/*  Utility
	================================ */

	/**
	 * Add route.
	 *
	 * @param string  $route
	 * @param callable $callback
	 * @param array   $args
	 * @deprecated since 0.20.0 and will be removed in 1.1
	 * @codeCoverageIgnore
	 */
	public static function add_route( $route, $callback, $args = array() ) {
		Helper::warn('Timber::add_route (and accompanying methods for load_view, etc. Have been deprecated and will soon be removed. Please update your theme with Route::map. You can read more in the 1.0 Upgrade Guide: https://github.com/timber/timber/wiki/1.0-Upgrade-Guide');
		\Routes::map($route, $callback, $args);
	}


}

/**
 * @param \Twig_Environment $twig
 * @return \Twig_Environment
 * @internal
 */
function do_legacy_twig_environment_filters_pre_timber_twig(\Twig_Environment $twig) {
	do_action('twig_apply_filters', $twig);
	do_action('timber/twig/filters', $twig);
}
// Attach action with lower than default priority to simulate the filters prior location before 'timber/twig' was fired at the bottom of Twig::add_timber_filters()
add_action('timber/twig', __NAMESPACE__.'\do_legacy_twig_environment_filters_pre_timber_twig', 5);

/**
 * @param \Twig_Environment $twig
 * @return \Twig_Environment
 * @internal
 */
function do_legacy_twig_environment_filters_post_timber_twig(\Twig_Environment $twig) {
	do_action('timber/twig/functions', $twig);
	do_action('timber/twig/escapers', $twig);
	do_action('timber/loader/twig', $twig);
}
// Attach action with higher than default priority to simulate the filters prior location after 'timber/twig' was fired at the bottom of Twig::add_timber_filters()
add_action('timber/twig', __NAMESPACE__.'\do_legacy_twig_environment_filters_post_timber_twig', 15);
