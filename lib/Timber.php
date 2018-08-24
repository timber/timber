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
 * Class Timber
 *
 * Main class called Timber for this plugin.
 *
 * @api
 * @example
 * ```php
 * $posts = new Timber\PostQuery();
 * $posts = new Timber\PostQuery( 'post_type = article' );
 * $posts = new Timber\PostQuery( array(
 *     'post_type' => 'article',
 *     'category_name' => 'sports',
 * ) );
 * $posts = new Timber\PostQuery( array( 23, 24, 35, 67 ), 'InkwellArticle' );
 *
 * $context = Timber::get_context();
 * $context['posts'] = $posts;
 *
 * Timber::render( 'index.twig', $context );
 * ```
 */
class Timber {

	public static $version = '2.0.0';
	public static $locations;
	public static $dirname = 'views';
	public static $twig_cache = false;
	public static $cache = false;
	public static $auto_meta = true;
	public static $autoescape = false;

	/**
	 * Global context cache.
	 *
	 * @var array An array containing global context variables.
	 */
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
			$this->init_constants();
			self::init();
		}
	}

	/**
	 * Tests whether we can use Timber
	 * @codeCoverageIgnore
	 */
	protected function test_compatibility() {
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
			new Integrations();

			/**
			 * Make an alias for the Timber class.
			 *
			 * This way, developers can use Timber::render() instead of Timber\Timber::render, which
			 * is more user-friendly.
			 */
			class_alias( 'Timber\Timber', 'Timber' );

			define('TIMBER_LOADED', true);
		}
	}

	/* Post Retrieval Routine
	================================ */

	/**
	 * Get a post by post ID or query (as a query string or an array of arguments).
	 *
	 * @api
	 * @deprecated since 2.0.0 Use `new Timber\Post()` instead.
	 *
	 * @param mixed        $query     Optional. Post ID or query (as query string or an array of
	 *                                arguments for WP_Query). If a query is provided, only the
	 *                                first post of the result will be returned. Default false.
	 * @param string|array $PostClass Optional. Class to use to wrap the returned post object.
	 *                                Default 'Timber\Post'.
	 *
	 * @return \Timber\Post|bool Timber\Post object if a post was found, false if no post was
	 *                           found.
	 */
	public static function get_post( $query = false, $PostClass = 'Timber\Post' ) {
		return PostGetter::get_post($query, $PostClass);
	}

	/**
	 * Get posts.
	 *
	 * @api
	 * @deprecated since 2.0.0 Use `new Timber\PostQuery()` instead.
	 *
	 * @param mixed        $query
	 * @param string|array $PostClass
	 *
	 * @return array|bool|null
	 */
	public static function get_posts( $query = false, $PostClass = 'Timber\Post', $return_collection = false ) {
		return PostGetter::get_posts($query, $PostClass, $return_collection);
	}

	/**
	 * Query post.
	 *
	 * @api
	 * @deprecated since 2.0.0 Use `new Timber\Post()` instead.
	 *
	 * @param mixed  $query
	 * @param string $PostClass
	 *
	 * @return array|bool|null
	 */
	public static function query_post( $query = false, $PostClass = 'Timber\Post' ) {
		return PostGetter::query_post($query, $PostClass);
	}

	/**
	 * Query posts.
	 *
	 * @api
	 * @deprecated since 2.0.0 Use `new Timber\PostQuery()` instead.
	 *
	 * @param mixed  $query
	 * @param string $PostClass
	 *
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
	 * Gets the context.
	 * 
	 * @api
	 * @deprecated 2.0.0, use `Timber::context()` instead.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public static function get_context( $args = array() ) {
		Helper::deprecated( 'get_context', 'context', '2.0.0' );

		return self::context( $args );
	}

	/**
	 * Gets the context.
	 *
	 * The context always contains the global context with the following variables:
	 *
	 * - `site` – An instance of `Timber\Site`.
	 * - `request` - An instance of `Timber\Request`.
	 * - `theme` - An instance of `Timber\Theme`.
	 * - `user` - An instance of `Timber\User`.
	 * - `http_host` - The HTTP host.
	 * - `wp_title` - Title retrieved for the currently displayed page, retrieved through
	 * `wp_title()`.
	 * - `body_class` - The body class retrieved through `get_body_class()`.
	 *
	 * The context can have additional variables based on which template is displayed. It will set
	 * `post` for singular pages, which is a `Timber\Object` of the currently displayed post. For
	 * archive pages, the context sets `posts`, which contains a collection of posts selected by the
	 * default query that WordPress runs for this archive. This behavior can be changed by passing
	 * arguments to this function.
	 *
	 * The global context will be cached, which means that you can call this function again without
	 * losing performance. Templated based contexts like `post` and `posts` will not be cached.
	 *
	 * @api
	 *
	 * @param array $args {
	 *     Optional. An array of arguments for the context.
	 *
	 *     @type false|null|\Timber\Post        $post                 A post ID, a WP_Post object, a `Timber\Post`
	 *                                                                object or a class instance that inherits from
	 *                                                                `Timber\Post`. If set to `false`, Timber will not
	 *                                                                set `post` in the context. Default `null`.
	 *     @type false|array|\Timber\PostQuery  $posts                An array of posts, a `Timber\PostQuery` object or
	 *                                                                an array of arguments that will be passed to
	 *                                                                `Timber\PostQuery`. If set to `false`, Timber will
	 *                                                                not set `posts` in the context. Default `array()`.
	 *     @type bool                           $cancel_default_query By default, parameters passed with `posts` will
	 *                                                                merge with the default WordPress post query. If
	 *                                                                this argument is set to `true`, merging will be
	 *                                                                disabled. Instead, the default query will be
	 *                                                                overwritten with the parameters passed in `posts`.
	 *                                                                Default `false`.
	 * }
	 *
	 * @return array An array of context variables that is used to pass into Twig templates through
	 *               a render or compile function.
	 */
	public static function context( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'post'                 => null,
			'posts'                => array(),
			'cancel_default_query' => false,
		) );

		/**
		 * Filters the default arguments for `Timber::context()`.
		 *
		 * @since 2.0.0
		 * @example
		 * ```php
         * // Globally disable `post` and `posts` in context
         * add_filter( 'timber/context/args', function( $args ) {
         * 	$args['post']  = false;
         * 	$args['posts'] = false;
         *     return $args;
         * } );
		 * ```
		 *
		 * @param array $args An array of arguments for the context. See `Timber::context()`.
		 */
		$args = apply_filters( 'timber/context/args', $args );

		$context = self::context_global();

		// Context for singular templates.
		$context_post = self::context_post( $args );

		if ( $context_post ) {
			$context['post'] = $context_post;
		}

		// Context for archive templates.
		$context_posts = self::context_posts( $args );

		if ( $context_posts ) {
			$context['posts'] = $context_posts;
		}

		return $context;
	}

	/**
	 * Gets the global context.
	 *
	 * This function is used by `Timber::context()` to get the global context. Usually, you don’t
	 * call this function directly, except when you need the global context in a partial view.
	 *
	 * The global context will be cached, which means that you can call this function again without
	 * losing performance.
	 *
	 * @api
	 * @since 2.0.0
	 * @example
	 * ```php
	 * add_shortcode( 'global_address', function() {
     *    return Timber::compile( 'global_address.twig', Timber::context_global() );
	 * } );
	 * ```
	 *
	 * @return array An array of global context variables.
	 */
	public static function context_global() {
		if ( empty( self::$context_cache ) ) {
			self::$context_cache['site']       = new Site();
			self::$context_cache['request']    = new Request();
			self::$context_cache['theme']      = self::$context_cache['site']->theme;
			self::$context_cache['user']       = is_user_logged_in() ? new User() : false;

			self::$context_cache['http_host']  = URLHelper::get_scheme() . '://' . URLHelper::get_host();
			self::$context_cache['wp_title']   = Helper::get_wp_title();
			self::$context_cache['body_class'] = implode( ' ', get_body_class() );

			/**
			 * Filters the global Timber context.
			 *
			 * By using this filter, you can add custom data to the global Timber context, which
			 * means that this data will be available on every page that is initialized with
			 * `Timber::get_context()`.
			 *
			 * Be aware that data will be cached as soon as you call `Timber::get_context()` for the
			 * first time. That’s why you should add this filter before you call
			 * `Timber::context()`.
			 *
			 * @see \Timber\Timber::get_context()
			 * @since 0.21.7
			 * @example
			 * ```php
			 * add_filter( 'timber/context', function( $context ) {
			 *     // Example: A custom value
			 *     $context['custom_site_value'] = 'Hooray!';
			 *
			 *     // Example: Add a menu to the global context.
			 *     $context['menu'] = new \Timber\Menu( 'primary-menu' );
			 *
			 *     // Example: Add all ACF options to global context.
			 *     $context['options'] = get_fields( 'options' );
			 *
			 *     return $context;
			 * } );
			 * ```
			 * ```twig
			 * <h1>{{ custom_site_value|e }}</h1>
			 *
			 * {% for item in menu.items %}
			 *     {# Display menu item #}
			 * {% endfor %}
			 *
			 * <footer>
			 *     {% if options.footer_text is not empty %}
			 *         {{ options.footer_text|e }}
			 *     {% endif %}
			 * </footer>
			 * ```
			 *
			 * @param array $context The global context.
			 */
			self::$context_cache = apply_filters( 'timber/context', self::$context_cache );

			/**
			 * Filters the global Timber context.
			 *
			 * @deprecated 2.0.0, use `timber/context`
			 */
			self::$context_cache = apply_filters_deprecated(
				'timber_context',
				array( self::$context_cache ),
				'2.0.0',
				'timber/context'
			);
		}

		return self::$context_cache;
	}

	/**
	 * Gets post context for a singular template.
	 *
	 * @api
	 * @since 2.0.0
	 *
	 * @param array $args An array of arguments from `Timber::context()`.
	 *
	 * @return null|\Timber\Post A `Timber\Post` object. Null if not applicable in the current
	 *                           context.
	 */
	public static function context_post( $args = array() ) {
		global $post;
		global $wp_query;

		/**
		 * Bail out if
		 * - A post shouldn’t be set in the context
		 * - We don’t have a singular template
		 */
		if ( false === $args['post'] || ! is_singular() ) {
			return null;
		}

		$context_post = $post;

		// Arguments that are passed directly to the context will always overwrite the default post.
		if ( ! empty( $args['post'] ) ) {
			$context_post = $args['post'];
		}

		/**
		 * Update cache if the cache is still empty or the post parameter passed in the args is
		 * different than the one from a previous call to this function.
		 */
		if ( ! $context_post instanceof Post ) {
			$context_post = new Post( $context_post );
		}

		// Mimick WordPress behavior to improve compatibility with third party plugins.
		$wp_query->in_the_loop = true;
		do_action_ref_array( 'loop_start', array( &$GLOBALS['wp_query'] ) );
		$wp_query->setup_postdata( $context_post->ID );

		return $context_post;
	}

	/**
	 * Gets posts context for an archive template.
	 *
	 * @api
	 * @since 2.0.0
	 *
	 * @param array $args An array of arguments from `Timber::context()`.
	 *
	 * @return array|null|\Timber\PostQuery
	 */
	public static function context_posts( $args = array() ) {
		global $wp_query;

		// Bail out if posts should not be set in context.
		if ( false === $args['posts'] ||
			( $args['cancel_default_query'] && empty( $args['posts'] ) )
		) {
			return null;
		}

		// Bail out if it’s not an archive page.
		if ( ! is_archive() && ! is_home() ) {
			return null;
		}

		// Use args from default query.
		$post_query_args = $wp_query->query_vars;

		if ( ! empty( $args['posts'] ) ) {
			if ( is_array( $args['post'] ) && ! $args['cancel_default_query'] ) {
				$post_query_args = wp_parse_args( $args['posts'], $post_query_args );
			} else {
				$post_query_args = $args['posts'];
			}
		}

		if ( $post_query_args instanceof PostQuery ) {
			return $post_query_args;
		}

		return new PostQuery( $post_query_args );
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
	 * @param array|string $filenames  Name of the Twig file to render. If this is an array of files, Timber will
	 *                                 render the first file that exists.
	 * @param array        $data       Optional. An array of data to use in Twig template.
	 * @param bool|int     $expires    Optional. In seconds. Use false to disable cache altogether. When passed an
	 *                                 array, the first value is used for non-logged in visitors, the second for users.
	 *                                 Default false.
	 * @param string       $cache_mode Optional. Any of the cache mode constants defined in Timber\Loader.
	 * @param bool         $via_render Optional. Whether to apply optional render or compile filters. Default false.
	 * @return bool|string The returned output.
	 */
	public static function compile( $filenames, $data = array(), $expires = false, $cache_mode = Loader::CACHE_USE_DEFAULT, $via_render = false ) {
		if ( !defined('TIMBER_LOADED') ) {
			self::init();
		}
		$caller = LocationManager::get_calling_script_dir(1);
		$loader = new Loader($caller);
		$file = $loader->choose_template($filenames);

		$caller_file = LocationManager::get_calling_script_file(1);

		/**
		 * Fires after the calling PHP file was determined in Timber’s compile
		 * function.
		 *
		 * This action is used by the Timber Debug Bar extension.
		 *
		 * @since 1.1.2
		 * @since 2.0.0 Switched from filter to action.
		 *
		 * @param string|null $caller_file The calling script file.
		 */
		do_action( 'timber/calling_php_file', $caller_file );

		if ( $via_render ) {
			/**
			 * Filters the Twig template that should be rendered.
			 *
			 * @since 2.0.0
			 *
			 * @param string $file The chosen Twig template name to render.
			 */
			$file = apply_filters( 'timber/render/file', $file );

			/**
			 * Filters the Twig file that should be rendered.
			 *
			 * @deprecated 2.0.0, use `timber/render/file`
			 */
			$file = apply_filters_deprecated(
				'timber_render_file',
				array( $file ),
				'2.0.0',
				'timber/render/file'
			);
		} else {
			/**
			 * Filters the Twig template that should be compiled.
			 *
			 * @since 2.0.0
			 *
			 * @param string $file The chosen Twig template name to compile.
			 */
			$file = apply_filters( 'timber/compile/file', $file );

			/**
			 * Filters the Twig template that should be compiled.
			 *
			 * @deprecated 2.0.0
			 */
			$file = apply_filters_deprecated(
				'timber_compile_file',
				array( $file ),
				'2.0.0',
				'timber/compile/file'
			);
		}

		$output = false;

		if ($file !== false) {
			if ( is_null($data) ) {
				$data = array();
			}

			if ( $via_render ) {
				/**
				 * Filters the data that should be passed for rendering a Twig template.
				 *
				 * @since 2.0.0
				 *
				 * @param array  $data The data that is used to render the Twig template.
				 * @param string $file The name of the Twig template to render.
				 */
				$data = apply_filters( 'timber/render/data', $data, $file );

				/**
				 * Filters the data that should be passed for rendering a Twig template.
				 *
				 * @deprecated 2.0.0
				 */
				$data = apply_filters_deprecated(
					'timber_render_data',
					array( $data ),
					'2.0.0',
					'timber/render/data'
				);
			} else {
				/**
				 * Filters the data that should be passed for compiling a Twig template.
				 *
				 * @since 2.0.0
				 *
				 * @param array  $data The data that is used to compile the Twig template.
				 * @param string $file The name of the Twig template to compile.
				 */
				$data = apply_filters( 'timber/compile/data', $data, $file );

				/**
				 * Filters the data that should be passed for compiling a Twig template.
				 *
				 * @deprecated 2.0.0, use `timber/compile/data`
				 */
				$data = apply_filters_deprecated(
					'timber_compile_data',
					array( $data ),
					'2.0.0',
					'timber/compile/data'
				);
			}

			$output = $loader->render($file, $data, $expires, $cache_mode);
		}

		/**
		 * Fires after a Twig template was compiled and before the compiled data
		 * is returned.
		 *
		 * This action can be helpful if you need to debug Twig template
		 * compilation.
		 *
		 * @todo Add parameter descriptions
		 *
		 * @since 2.0.0
		 *
		 * @param string $output
		 * @param string $file
		 * @param array  $data
		 * @param bool   $expires
		 * @param string $cache_mode
		 */
		do_action( 'timber/compile/done', $output, $file, $data, $expires, $cache_mode );

		/**
		 * Fires after a Twig template was compiled and before the compiled data
		 * is returned.
		 *
		 * @deprecated 2.0.0, use `timber/compile/done`
		 */
		do_action_deprecated( 'timber_compile_done', array(), '2.0.0', 'timber/compile/done' );

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
	 * @param array  $data   Optional. An array of data to use in Twig template.
	 * @return bool|string
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
	 * @todo In case this isn’t deprecated for 2.0.0, update filter hook name.
	 *
	 * @api
	 * @param array|string $filenames  Name of the Twig file to render. If this is an array of files, Timber will
	 *                                 render the first file that exists.
	 * @param array        $data       Optional. An array of data to use in Twig template.
	 * @param bool|int     $expires    Optional. In seconds. Use false to disable cache altogether. When passed an
	 *                                 array, the first value is used for non-logged in visitors, the second for users.
	 *                                 Default false.
	 * @param string       $cache_mode Optional. Any of the cache mode constants defined in Timber\Loader.
	 * @return bool|string The returned output.
	 */
	public static function fetch( $filenames, $data = array(), $expires = false, $cache_mode = Loader::CACHE_USE_DEFAULT ) {
		$output = self::compile($filenames, $data, $expires, $cache_mode, true);

		/**
		 * Filters the compiled result before it is returned.
		 *
		 * @todo Maybe deprecate in 2.0?
		 * @see \Timber\Timber::fetch()
		 * @since 0.16.7
		 *
		 * @param string $output The compiled output.
		 */
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
	 * @param array|string $filenames  Name of the Twig file to render. If this is an array of files, Timber will
	 *                                 render the first file that exists.
	 * @param array        $data       Optional. An array of data to use in Twig template.
	 * @param bool|int     $expires    Optional. In seconds. Use false to disable cache altogether. When passed an
	 *                                 array, the first value is used for non-logged in visitors, the second for users.
	 *                                 Default false.
	 * @param string       $cache_mode Optional. Any of the cache mode constants defined in Timber\Loader.
	 * @return bool|string The echoed output.
	 */
	public static function render( $filenames, $data = array(), $expires = false, $cache_mode = Loader::CACHE_USE_DEFAULT ) {
		$output = self::fetch($filenames, $data, $expires, $cache_mode);
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
		$caller = LocationManager::get_calling_script_dir(1);
		$uris = LocationManager::get_locations($caller);
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

	/**
	 * Get pagination.
	 *
	 * @api
	 * @param array $prefs an array of preference data.
	 * @return array|mixed
	 */
	public static function get_pagination( $prefs = array() ) {
		return Pagination::get_pagination($prefs);
	}
}
