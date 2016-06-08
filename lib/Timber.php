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
use Timber\Request;
use Timber\User;
use Timber\Loader;

/**
 * Timber Class.
 *
 * Main class called Timber for this plugin.
 *
 * Usage:
 *  $posts = Timber::get_posts();
 *  $posts = Timber::get_posts('post_type = article')
 *  $posts = Timber::get_posts(array('post_type' => 'article', 'category_name' => 'sports')); // uses wp_query format.
 *  $posts = Timber::get_posts(array(23,24,35,67), 'InkwellArticle');
 *
 *  $context = Timber::get_context(); // returns wp favorites!
 *  $context['posts'] = $posts;
 *  Timber::render('index.twig', $context);
 */
class Timber {

	public static $version = '1.0.3';
	public static $locations;
	public static $dirname;
	public static $twig_cache = false;
	public static $cache = false;
	public static $auto_meta = true;
	public static $autoescape = false;

	public static $context_cache = array();

	/**
	 * @codeCoverageIgnore
	 */
	public function __construct() {
		if ( !defined('ABSPATH') ) {
			return;
		}
		if ( class_exists('\WP') && !defined('TIMBER_LOADED') ) {
			$this->test_compatibility();
			$this->backwards_compatibility();
			$this->init_constants();
			$this::init();
		}
	}

	/**
	 * Tests whether we can use Timber
	 * @codeCoverageIgnore
	 * @return
	 */
	protected function test_compatibility() {
		if ( is_admin() || $_SERVER['PHP_SELF'] == '/wp-login.php' ) {
			return;
		}
		if ( version_compare(phpversion(), '5.3.0', '<') && !is_admin() ) {
			trigger_error('Timber requires PHP 5.3.0 or greater. You have '.phpversion(), E_USER_ERROR);
		}
		if ( !class_exists('Twig_Autoloader') ) {
			trigger_error('You have not run "composer install" to download required dependencies for Timber, you can read more on https://github.com/timber/timber#installation', E_USER_ERROR);
		}
	}

	private function backwards_compatibility() {
		if ( class_exists('TimberArchives') ) {
			//already run, so bail
			return;
		}
		$names = array('Archives', 'Comment', 'Core', 'FunctionWrapper', 'Helper', 'Image', 'ImageHelper', 'Integrations', 'Loader', 'Menu', 'MenuItem', 'Post', 'PostGetter', 'PostsCollection', 'QueryIterator', 'Request', 'Site', 'Term', 'TermGetter', 'Theme', 'Twig', 'URLHelper', 'User', 'Integrations\Command', 'Integrations\ACF');
		foreach ( $names as $name ) {
			$old_class_name = 'Timber'.str_replace('Integrations\\', '', $name);
			$new_class_name = 'Timber\\'.$name;
			if ( class_exists($new_class_name) ) {
				class_alias($new_class_name, $old_class_name);
			}
		}
		class_alias(get_class($this), 'Timber');
		if ( class_exists('Timber\\'.'Integrations\Timber_WP_CLI_Command') ) {
			class_alias('Timber\\'.'Integrations\Timber_WP_CLI_Command', 'Timber_WP_CLI_Command');
		}
	}

	public function init_constants() {
		defined("TIMBER_LOC") or define("TIMBER_LOC", realpath(dirname(__DIR__)));
	}

	/**
	 * @codeCoverageIgnore
	 */
	protected static function init() {
		if ( class_exists('\WP') && !defined('TIMBER_LOADED') ) {
			Twig::init();
			ImageHelper::init();
			Admin::init();
			Integrations::init();
			define('TIMBER_LOADED', true);
		}
	}

	/* Post Retrieval Routine
	================================ */

	/**
	 * Get post.
	 *
	 * @param mixed   $query
	 * @param string  $PostClass
	 * @return array|bool|null
	 */
	public static function get_post( $query = false, $PostClass = 'Timber\Post' ) {
		return PostGetter::get_post($query, $PostClass);
	}

	/**
	 * Get posts.
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
	 *
	 * @param mixed   $query
	 * @param string  $PostClass
	 * @return array|bool|null
	 */
	public static function query_post( $query = false, $PostClass = 'Timber\Post' ) {
		return PostGetter::query_post($query, $PostClass);
	}

	/**
	 * Query posts.
	 *
	 * @param mixed   $query
	 * @param string  $PostClass
	 * @return array|bool|null
	 */
	public static function query_posts( $query = false, $PostClass = 'Timber\Post' ) {
		return PostGetter::query_posts($query, $PostClass);
	}

	/* Term Retrieval
	================================ */

	/**
	 * Get terms.
	 *
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
	 *
	 * @param int|WP_Term|object $term
	 * @param string     $taxonomy
	 * @return Timber\Term|WP_Error|null
	 */
	public static function get_term( $term, $taxonomy = 'post_tag', $TermClass = 'Timber\Term' ) {
		return TermGetter::get_term( $term, $taxonomy, $TermClass );
	}

	/* Site Retrieval
	================================ */

	/**
	 * Get sites.
	 *
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
	 *
	 * @return array
	 */
	public static function get_context() {
		if ( empty(self::$context_cache) ) {
			self::$context_cache['http_host'] = 'http://'.URLHelper::get_host();
			self::$context_cache['wp_title'] = Helper::get_wp_title();
			self::$context_cache['wp_head'] = Helper::function_wrapper('wp_head');
			self::$context_cache['wp_footer'] = Helper::function_wrapper('wp_footer');
			self::$context_cache['body_class'] = implode(' ', get_body_class());

			self::$context_cache['site'] = new Site();
			self::$context_cache['request'] = new Request();
			$user = new User();
			self::$context_cache['user'] = ($user->ID) ? $user : false;
			self::$context_cache['theme'] = self::$context_cache['site']->theme;

			self::$context_cache['posts'] = Timber::query_posts();

			self::$context_cache = apply_filters('timber_context', self::$context_cache);
			self::$context_cache = apply_filters('timber/context', self::$context_cache);
		}


		return self::$context_cache;
	}

	/**
	 * Compile function.
	 *
	 * @param array   $filenames
	 * @param array   $data
	 * @param bool    $expires
	 * @param string  $cache_mode
	 * @param bool    $via_render
	 * @return bool|string
	 */
	public static function compile( $filenames, $data = array(), $expires = false, $cache_mode = Loader::CACHE_USE_DEFAULT, $via_render = false ) {
		if ( !defined('TIMBER_LOADED') ) {
			self::init();
		}
		$caller = self::get_calling_script_dir();
		$loader = new Loader($caller);
		$file = $loader->choose_template($filenames);
		$output = '';
		if ( is_null($data) ) {
			$data = array();
		}
		if ( strlen($file) ) {
			if ( $via_render ) {
				$file = apply_filters('timber_render_file', $file);
				$data = apply_filters('timber_render_data', $data);
			} else {
				$file = apply_filters('timber_compile_file', $file);
				$data = apply_filters('timber_compile_data', $data);
			}
			$output = $loader->render($file, $data, $expires, $cache_mode);
		}
		do_action('timber_compile_done');
		return $output;
	}

	/**
	 * Compile string.
	 *
	 * @param string  $string a string with twig variables.
	 * @param array   $data   an array with data in it.
	 * @return  bool|string
	 */
	public static function compile_string( $string, $data = array() ) {
		$dummy_loader = new Loader();
		$twig = $dummy_loader->get_twig();
		$template = $twig->createTemplate($string);
		return $template->render($data);
	}

	/**
	 * Fetch function.
	 *
	 * @param array   $filenames
	 * @param array   $data
	 * @param bool    $expires
	 * @param string  $cache_mode
	 * @return bool|string
	 */
	public static function fetch( $filenames, $data = array(), $expires = false, $cache_mode = Loader::CACHE_USE_DEFAULT ) {
		if ( $expires === true ) {
			//if this is reading as true; the user probably is using the old $echo param
			//so we should move all vars up by a spot
			$expires = $cache_mode;
			$cache_mode = Loader::CACHE_USE_DEFAULT;
		}
		$output = self::compile($filenames, $data, $expires, $cache_mode, true);
		$output = apply_filters('timber_compile_result', $output);
		return $output;
	}

	/**
	 * Render function.
	 *
	 * @param array   $filenames
	 * @param array   $data
	 * @param bool    $expires
	 * @param string  $cache_mode
	 * @return bool|string
	 */
	public static function render( $filenames, $data = array(), $expires = false, $cache_mode = Loader::CACHE_USE_DEFAULT ) {
		$output = self::fetch($filenames, $data, $expires, $cache_mode);
		echo $output;
		return $output;
	}

	/**
	 * Render string.
	 *
	 * @param string  $string a string with twig variables.
	 * @param array   $data   an array with data in it.
	 * @return  bool|string
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
	 *
	 * @param string  $sidebar
	 * @param array   $data
	 * @return bool|string
	 */
	public static function get_sidebar( $sidebar = '', $data = array() ) {
		if ( $sidebar == '' ) {
			$sidebar = 'sidebar.php';
		}
		if ( strstr(strtolower($sidebar), '.php') ) {
			return self::get_sidebar_from_php($sidebar, $data);
		}
		return self::compile($sidebar, $data);
	}

	/**
	 * Get sidebar from PHP
	 *
	 * @param string  $sidebar
	 * @param array   $data
	 * @return string
	 */
	public static function get_sidebar_from_php( $sidebar = '', $data ) {
		$caller = self::get_calling_script_dir();
		$loader = new Loader();
		$uris = $loader->get_locations($caller);
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
	 * @param int     $widget_id
	 * @return TimberFunctionWrapper
	 */
	public static function get_widgets( $widget_id ) {
		return trim(Helper::function_wrapper('dynamic_sidebar', array($widget_id), true));
	}

	/*  Pagination
	================================ */

	/**
	 * Get pagination.
	 *
	 * @param array   $prefs
	 * @return array mixed
	 */
	public static function get_pagination( $prefs = array() ) {
		global $wp_query;
		global $paged;
		global $wp_rewrite;
		$args = array();
		$args['total'] = ceil($wp_query->found_posts / $wp_query->query_vars['posts_per_page']);
		if ( $wp_rewrite->using_permalinks() ) {
			$url = explode('?', get_pagenum_link(0));
			if ( isset($url[1]) ) {
				parse_str($url[1], $query);
				$args['add_args'] = $query;
			}
			$args['format'] = $wp_rewrite->pagination_base.'/%#%';
			$args['base'] = trailingslashit($url[0]).'%_%';
		} else {
			$big = 999999999;
			$args['base'] = str_replace($big, '%#%', esc_url(get_pagenum_link($big)));
		}
		$args['type'] = 'array';
		$args['current'] = max(1, get_query_var('paged'));
		$args['mid_size'] = max(9 - $args['current'], 3);
		if ( is_int($prefs) ) {
			$args['mid_size'] = $prefs - 2;
		} else {
			$args = array_merge($args, $prefs);
		}
		$data = array();
		$data['current'] = $args['current'];
		$data['total'] = $args['total'];
		$data['pages'] = Helper::paginate_links($args);
		// decrement current so that it matches up with the 0 based index used by the pages array
		$current = $data['current'] - 1;
		// set next and prev using pages array generated by paginate links
		if ( isset( $data['pages'][$current + 1] ) ) {
			$data['next'] = array('link' => untrailingslashit( $data['pages'][$current + 1]['link'] ), 'class' => 'page-numbers next');
		}
		if ( isset( $data['pages'][$current - 1] ) ) {
			$data['prev'] = array('link' => untrailingslashit( $data['pages'][$current - 1]['link'] ), 'class' => 'page-numbers prev');
		}
		if ( $paged < 2 ) {
			$data['prev'] = '';
		}
		if ( $data['total'] === (double) 0 ) {
			$data['next'] = '';
		}
		return $data;
	}

	/*  Utility
	================================ */

	/**
	 * Get calling script dir.
	 *
	 * @return string
	 */
	public static function get_calling_script_dir( $offset = 0 ) {
		$caller = self::get_calling_script_file($offset);
		if ( !is_null($caller) ) {
			$pathinfo = pathinfo($caller);
			$dir = $pathinfo['dirname'];
			return $dir;
		}
	}


	/**
	 * Add route.
	 *
	 * @param string  $route
	 * @param callable $callback
	 * @param array   $args
	 * @deprecated since 0.20.0 and will be removed in 1.1
	 */
	public static function add_route( $route, $callback, $args = array() ) {
		Helper::warn('Timber::add_route (and accompanying methods for load_view, etc. Have been deprecated and will soon be removed. Please update your theme with Route::map. You can read more in the 1.0 Upgrade Guide: https://github.com/timber/timber/wiki/1.0-Upgrade-Guide');
		\Routes::map($route, $callback, $args);
	}

	/**
	 * Get calling script file.
	 *
	 * @param int     $offset
	 * @return string|null
	 * @deprecated since 0.20.0
	 */
	public static function get_calling_script_file( $offset = 0 ) {
		$caller = null;
		$backtrace = debug_backtrace();
		$i = 0;
		foreach ( $backtrace as $trace ) {
			if ( array_key_exists('file', $trace) && $trace['file'] != __FILE__ ) {
				$caller = $trace['file'];
				break;
			}
			$i++;
		}
		if ( $offset ) {
			$caller = $backtrace[$i + $offset]['file'];
		}
		return $caller;
	}
}
