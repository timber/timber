<?php

namespace Timber;

use Timber\Factory\CommentFactory;
use Timber\Factory\UserFactory;

/**
 * Class Timber
 *
 * Main class called Timber for this plugin.
 *
 * @api
 * @example
 * ```php
 * $posts = Timber::get_posts();
 * $posts = Timber::get_posts( 'post_type = article' );
 * $posts = Timber::get_posts( [
 *     'post_type'     => 'article',
 *     'category_name' => 'sports',
 * ] );
 * $posts = Timber::get_posts( [ 23, 24, 35, 67 ] );
 *
 * $context = Timber::context();
 * $context['posts'] = $posts;
 *
 * Timber::render( 'index.twig', $context );
 * ```
 */
class Timber {

	public static $version = '2.0.0';
	public static $locations;
	public static $dirname = 'views';
	public static $auto_meta = true;

	/**
	 * Global context cache.
	 *
	 * @var array An array containing global context variables.
	 */
	public static $context_cache = array();

	/**
	 * Caching option for Twig.
	 *
	 * @deprecated 2.0.0
	 * @var bool
	 */
	public static $twig_cache = false;

	/**
	 * Caching option for Twig.
	 *
	 * Alias for `Timber::$twig_cache`.
	 *
	 * @deprecated 2.0.0
	 * @var bool
	 */
	public static $cache = false;

	/**
	 * Autoescaping option for Twig.
	 *
	 * @deprecated 2.0.0
	 * @var bool
	 */
	public static $autoescape = false;

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
		if ( ! class_exists( 'Twig\Token' ) ) {
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
	 * Gets a Timber Post by post ID or WP_Post object.
	 *
	 * By default, Timber will use the `Timber\Post` class to create a new post object. To control
	 * which class is used for your post object, use [Class Maps]().
	 *
	 * @api
	 * @example
	 * ```php
	 * // Using a post ID.
	 * $post = Timber::get_post( 75 );
	 *
	 * // Using a WP_Post object.
	 * $post = Timber::get_post( $wp_post );
	 *
	 * // Use currently queried post. Same as using get_the_ID() as a parameter.
	 * $post = Timber::get_post();
	 * ```
	 *
	 * @todo Add links to Class Maps documentation in function summary.
	 * @todo Remove warnings in Timber 3.0
	 *
	 * @param null|int|\WP_Post $post Optional. Post ID or WP_Post object to get a Timber Post
	 *                                object. Default `null`.
	 *
	 * @return \Timber\Post|false `Timber\Post` object or an instance of a child class of
	 *                            `Timber\Post` if a post was found, `false` if no post was found.
	 */
	public static function get_post( $post = null ) {
		if ( is_string( $post ) && ! is_numeric( $post ) ) {
			Helper::doing_it_wrong(
				'Timber::get_post()',
				'Getting a post by post slug or post name was removed from Timber::get_post() in Timber 2.0. Use Timber::get_post_by() instead.',
				'2.0.0'
			);
		}

		if ( func_num_args() > 1 ) {
			// @todo Add link to Class Maps documentation.
			Helper::doing_it_wrong(
				'Timber::get_post()',
				'The $PostClass parameter for passing in the post class to use in Timber::get_post() was removed in Timber 2.0. Use Class Maps (LINK HERE) instead.',
				'2.0.0'
			);
		}

		if ( null === $post ) {
			$post = get_the_ID();
		}

		// @todo Use factory.
		return PostGetter::get_post( $post, 'Timber\Post' );
	}

	/**
	 * Gets a collection of Timber posts by query.
	 *
	 * By default, Timber will use the `Timber\Post` class to create a your post objects. To control
	 * which class is used for your post objects, use [Class Maps]().
	 *
	 * @api
	 * @example
	 * ```php
	 * // Using the global query.
	 * $posts = Timber::get_posts();
	 *
	 * // Using the WP_Query argument format.
	 * $posts = Timber::get_posts( [
	 *     'post_type'     => 'article',
	 *     'category_name' => 'sports',
	 * ] );
	 * ```
	 * @todo Remove warnings in Timber 3.0
	 *
	 * @param array|false $query   Optional. A WordPress-style query. Use an array in the same way
	 *                             you would use the `$args` parameter in
	 *                             [WP_Query](https://developer.wordpress.org/reference/classes/wp_query/).
	 *                             Passing no parameter of `false` will use the current global
	 *                             query. Default `false`.
	 * @param array       $options {
	 *     An array of options for the query.
	 *
	 *     @type bool $merge_default Optional. Whether to merge the arguments passed in `query` with
	 *                               the default arguments that WordPress uses for the current
	 *                               template. Default `false`.
	 * }
	 *
	 * @return PostCollection|false
	 */
	public static function get_posts( $query = false, $options = [] ) {
		if ( is_string( $query ) ) {
			// @todo Add link to documentation for get_posts().
			Helper::doing_it_wrong(
				'Timber::get_posts()',
				"Querying posts by using a query string was removed in Timber 2.0. Pass in the query string as an options array instead. For example, change Timber::get_posts( 'post_type=portfolio&posts_per_page=3') to Timber::get_posts( [ 'post_type' => 'portfolio', 'posts_per_page' => 3 ] ).",
				'2.0.0'
			);
		}

		if ( is_string( $options ) ) {
			// @todo Add link to Class Maps documentation.
			Helper::doing_it_wrong(
				'Timber::get_posts()',
				'The $PostClass parameter for passing in the post class to use in Timber::get_posts() was removed in Timber 2.0. Use Class Maps (LINK HERE) instead.',
				'2.0.0'
			);
		}

		if ( 3 === func_num_args() ) {
			Helper::doing_it_wrong(
				'Timber::get_posts()',
				'The $return_collection parameter to control whether a post collection is returned in Timber::get_posts() was removed in Timber 2.0.',
				'2.0.0'
			);
		}

		/**
		 * @todo Define all default $options.
		 * @todo Actually apply options.
		 */
		$options = wp_parse_args( $options, [
			'merge_default' => false,
		] );

		// @todo Use factory.
		return PostGetter::get_posts( $query, 'Timber\Post', true );
	}

	/**
	 * Query post.
	 *
	 * @api
	 * @deprecated since 2.0.0 Use `Timber::get_post()` instead.
	 *
	 * @param false|int|\WP_Post $post
	 *
	 * @return \Timber\Post|false
	 */
	public static function query_post( $post = false ) {
		Helper::deprecated( 'Timber::query_post()', 'Timber::get_post()', '2.0.0' );

		return self::get_post( $post );
	}

	/**
	 * Query posts.
	 *
	 * @api
	 * @deprecated since 2.0.0 Use `Timber::get_posts()` instead.
	 *
	 * @param array|false $query
	 *
	 * @return array|false
	 */
	public static function query_posts( $query = false ) {
		Helper::deprecated( 'Timber::query_posts()', 'Timber::get_posts()', '2.0.0' );

		return self::get_posts( $query );
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
	 * @param int|\WP_Term|object $term
	 * @param string              $taxonomy
	 * @return \Timber\Term|\WP_Error|null
	 */
	public static function get_term( $term, $taxonomy = 'post_tag', $TermClass = 'Timber\Term' ) {
		return TermGetter::get_term($term, $taxonomy, $TermClass);
	}

	/* User Retrieval
	================================ */

	/**
	 * Gets one or more users as an array.
	 *
	 * By default, Timber will use the `Timber\User` class to create a your post objects. To
	 * control which class is used for your post objects, use [Class Maps]().
	 *
	 * @api
	 * @since 2.0.0
	 * @example
	 * ```php
	 * // Get users with on an array of user IDs.
	 * $users = Timber::get_users( [ 24, 81, 325 ] );
	 *
	 * // Get all users that only have a subscriber role.
	 * $subscribers = Timber::get_users( [
	 *     'role' => 'subscriber',
	 * ] );
	 *
	 * // Get all users that have published posts.
	 * $post_authors = Timber::get_users( [
	 *     'has_published_posts' => [ 'post' ],
	 * ] );
	 * ```
	 *
	 * @todo  Add links to Class Maps documentation in function summary.
	 *
	 * @param array $query   Optional. A WordPress-style query or an array of user IDs. Use an
	 *                       array in the same way you would use the `$args` parameter in
	 *                       [WP_User_Query](https://developer.wordpress.org/reference/classes/wp_user_query/).
	 *                       See
	 *                       [WP_User_Query::prepare_query()](https://developer.wordpress.org/reference/classes/WP_User_Query/prepare_query/)
	 *                       for a list of all available parameters. Passing an empty parameter
	 *                       will return an empty array. Default empty array
	 *                       `[]`.
	 * @param array $options Optional. An array of options. None are currently supported. This
	 *                       parameter exists to prevent future breaking changes. Default empty
	 *                       array `[]`.
	 *
	 * @return \Iterable An array of users objects. Will be empty if no users were found.
	 */
	public static function get_users( array $query = [], array $options = [] ) : Iterable {
		$factory = new UserFactory();
		// TODO return a Collection type?
		return $factory->from($query);
	}

	/**
	 * Gets a single user.
	 *
	 * By default, Timber will use the `Timber\User` class to create a your post objects. To
	 * control which class is used for your post objects, use [Class Maps]().
	 *
	 * @api
	 * @since 2.0.0
	 * @example
	 * ```php
	 * $current_user = Timber::get_user();
	 *
	 * // Get user by ID.
	 * $user = Timber::get_user( $user_id );
	 *
	 * // Convert a WP_User object to a Timber\User object.
	 * $user = Timber::get_user( $wp_user_object );
	 *
	 * // Check if a user is logged in.
	 *
	 * $user = Timber::get_user();
	 *
	 * if ( $user ) {
	 *     // Yay, user is logged in.
	 * }
	 * ```
	 *
	 * @todo Add links to Class Maps documentation in function summary.
	 *
	 * @param int|\WP_User $user A WP_User object or a WordPress user ID. Defaults to the ID of the
	 *                           currently logged-in user.
	 *
	 * @return \Timber\User|false
	 */
	public static function get_user( $user = null ) {
		/*
		 * TODO in the interest of time, I'm implementing this logic here. If there's
		 * a better place to do this or something that already implements this, let me know
		 * and I'll switch over to that.
		 */
		$user = $user ?: get_current_user_id();

		$factory = new UserFactory();
		return $factory->from($user);
	}

	/**
	 * Gets a user by field.
	 *
	 * This function works like
	 * [`get_user_by()`](https://developer.wordpress.org/reference/functions/get_user_by/), but
	 * returns a `Timber\User` object.
	 *
	 * @api
	 * @since 2.0.0
	 * @example
	 * ```php
	 * // Get a user by email.
	 * $user = Timber::get_user_by( 'email', 'user@example.com' );
	 *
	 * // Get a user by login.
	 * $user = Timber::get_user_by( 'login', 'keanu-reeves' );
	 * ```
	 *
	 * @param string     $field The name of the field to retrieve the user with. One of: `id`,
	 *                          `ID`, `slug`, `email` or `login`.
	 * @param int|string $value The value to search for by `$field`.
	 *
	 * @return \Timber\User|null
	 */
	public static function get_user_by( string $field, $value ) {
		$wp_user = get_user_by($field, $value);

		if ($wp_user === false) {
			return false;
		}

		return static::get_user($wp_user);
	}

	/* Comment Retrieval
	================================ */

	/**
	 * Get comments.
	 * @api
	 * @param array   $query
	 * @param array   $options optional; none are currently supported
	 * @return mixed
	 */
	public static function get_comments( array $query = [], array $options = [] ) : Iterable {
		$factory = new CommentFactory();
		// TODO return a Collection type?
		return $factory->from($query);
	}

	/**
	 * Get comment.
	 * @api
	 * @param int|\WP_Comment $comment
	 * @return \Timber\Comment|null
	 */
	public static function get_comment( $comment ) {
		$factory = new CommentFactory();
		return $factory->from($comment);
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
	 * @deprecated 2.0.0, use `Timber::context()` instead.
	 *
	 * @return array
	 */
	public static function get_context() {
		Helper::deprecated( 'get_context', 'context', '2.0.0' );

		return self::context();
	}

	/**
	 * Gets the global context.
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
	 * The global context will be cached, which means that you can call this function again without
	 * losing performance.
	 *
	 * Additionally to that, the context will contain template contexts depending on which template
	 * is being displayed. For archive templates, a `posts` variable will be present that will
	 * contain a collection of `Timber\Post` objects for the default query. For singular templates,
	 * a `post` variable will be present that that contains a `Timber\Post` object of the `$post`
	 * global.
	 *
	 * @api
	 * @since 2.0.0
	 *
	 * @return array An array of context variables that is used to pass into Twig templates through
	 *               a render or compile function.
	 */
	public static function context() {
		$context = self::context_global();

		if ( is_singular() ) {
			$post = ( new Post() )->setup();
			$context['post'] = $post;
		} elseif ( is_archive() || is_home() ) {
			$context['posts'] = new PostQuery();
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
	 *     return Timber::compile(
	 *         'global_address.twig',
	 *         Timber::context_global()
	 *     );
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
			self::$context_cache['user']       = is_user_logged_in() ? static::get_user() : false;

			self::$context_cache['http_host']  = URLHelper::get_scheme() . '://' . URLHelper::get_host();
			self::$context_cache['wp_title']   = Helper::get_wp_title();
			self::$context_cache['body_class'] = implode( ' ', get_body_class() );

			/**
			 * Filters the global Timber context.
			 *
			 * By using this filter, you can add custom data to the global Timber context, which
			 * means that this data will be available on every page that is initialized with
			 * `Timber::context()`.
			 *
			 * Be aware that data will be cached as soon as you call `Timber::context()` for the
			 * first time. That’s why you should add this filter before you call
			 * `Timber::context()`.
			 *
			 * @see \Timber\Timber::context()
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
			 * @codeCoverageIgnore
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
				 * @codeCoverageIgnore
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

			$output = $loader->render( $file, $data, $expires, $cache_mode );
		}

		/**
		 * Filters the compiled result before it is returned in `Timber::compile()`.
		 *
		 * It adds the posibility to filter the output ready for render.
		 *
		 * @since 2.0.0
		 *
		 * @param string $output
		 */
		$output = apply_filters( 'timber/compile/result', $output );

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
	 * @api
	 * @deprecated 2.0.0 use Timber::compile()
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
		Helper::deprecated(
			'fetch',
			'Timber::compile() (see https://timber.github.io/docs/reference/timber/#compile for more information)',
			'2.0.0'
		);
		$output = self::compile( $filenames, $data, $expires, $cache_mode, true );

		/**
		 * Filters the compiled result before it is returned.
		 *
		 * @see \Timber\Timber::fetch()
		 * @since 0.16.7
		 * @deprecated 2.0.0 use timber/compile/result
		 *
		 * @param string $output The compiled output.
		 */
		$output = apply_filters_deprecated(
			'timber_compile_result',
			array( $output ),
			'2.0.0',
			'timber/compile/result'
		);

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
	 * $context = Timber::context();
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
		$output = self::compile($filenames, $data, $expires, $cache_mode, true);
		echo $output;
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
		$caller = LocationManager::get_calling_script_dir( 1 );
		$uris   = LocationManager::get_locations( $caller );
		ob_start();
		$found = false;
		foreach ( $uris as $namespace => $uri_locations ) {
			if ( is_array( $uri_locations ) ) {
				foreach ( $uri_locations as $uri ) {
					if ( file_exists( trailingslashit( $uri ) . $sidebar ) ) {
						include trailingslashit( $uri ) . $sidebar;
						$found = true;
					}
				}
			}
		}
		if ( ! $found ) {
			Helper::error_log( 'error loading your sidebar, check to make sure the file exists' );
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
	 * @deprecated 2.0.0
	 * @link https://timber.github.io/docs/guides/pagination/
	 * @param array $prefs an array of preference data.
	 * @return array|mixed
	 */
	public static function get_pagination( $prefs = array() ) {
		Helper::deprecated(
			'get_pagination',
			'{{ posts.pagination }} (see https://timber.github.io/docs/guides/pagination/ for more information)',
			'2.0.0'
		);

		return Pagination::get_pagination($prefs);
	}

}
