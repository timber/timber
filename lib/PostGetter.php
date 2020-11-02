<?php

namespace Timber;

/**
 * Class PostGetter
 */
class PostGetter {

	/**
	 * @param mixed        $query
	 * @param string|array $PostClass
	 * @return \Timber\Post|bool
	 */
	public static function get_post( $query = false, $PostClass = '\Timber\Post' ) {
		// if a post id is passed, grab the post directly
		if ( is_numeric($query) ) {
			// @todo this will become:
			//$factory = new PostFactory();
			//$post = $factory->get_post($query);
			$post_type = get_post_type($query);
			$PostClass = PostGetter::get_post_class($post_type, $PostClass);
			$post = new $PostClass($query);
			// get the latest revision if we're dealing with a preview
			$posts = PostCollection::maybe_set_preview(array($post));
			if ( $post = reset($posts) ) {
				return $post;
			}
		}

		$posts = self::get_posts($query, $PostClass);

		if ( is_iterable($posts) && $post = reset($posts) ) {
			return $post;
		}

		return false;
	}

	/**
	 * get_post_id_by_name($post_name)
	 * @internal
	 * @since 1.5.0
	 * @param string $post_name
	 * @return int
	 */
	public static function get_post_id_by_name( $post_name ) {
		global $wpdb;
		$query = $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_name = %s LIMIT 1", $post_name);
		$result = $wpdb->get_row($query);
		if ( !$result ) {
			return null;
		}
		return $result->ID;
	}

	public static function get_posts( $query = false, $PostClass = '\Timber\Post', $return_collection = false ) {

		/**
		 * Filters whether Timber::get_posts() should mirror WordPress’s get_posts() function.
		 *
		 * When passing `true` in this filter, Timber will set the following parameters for your query:
		 *
		 * - `ignore_sticky_posts = true`
		 * - `suppress_filters = true`
		 * - `no_found_rows = true`
		 *
		 * @since 1.9.5
		 * @deprecated 2.0.0 use the desired args within Timber\PostQuery() instead
		 * @example
		 * ```php
		 * add_filter( 'timber/get_posts/mirror_wp_get_posts', '__return_true' );
		 * ```
		 *
		 * @param bool $mirror Whether to mirror the `get_posts()` function of WordPress with all its
		 *                     parameters. Default `false`.
		 */
		$mirror_wp_get_posts = apply_filters( 'timber/get_posts/mirror_wp_get_posts', false );
		if ( $mirror_wp_get_posts ) {
			add_filter( 'pre_get_posts', array('Timber\PostGetter', 'set_query_defaults') );
		}

		$posts = self::query_posts($query, $PostClass);

		/**
		 * Filters the posts returned by `Timber::get_posts()`.
		 *
		 * There’s no replacement for this filter, because it’s called in a function that will be
		 * removed in the future. If you’re using `Timber::get_posts()`, you should replace it with
		 * `new Timber\PostQuery()`.
		 *
		 * @deprecated 2.0.0
		 */
		$posts = apply_filters_deprecated(
			'timber_post_getter_get_posts',
			array( $posts->get_posts( $return_collection ) ),
			'2.0.0',
			false,
			'There’s no replacement for this filter, because it’s called in a function that will be removed. If you’re using Timber::get_posts(), you should replace it with new Timber\PostQuery().'
		);

		return $posts;
	}

	public static function query_post( $query = false, $PostClass = '\Timber\Post' ) {
		$posts = self::query_posts($query, $PostClass);
		if ( method_exists($posts, 'current') && $post = $posts->current() ) {
			return $post;
		}
	}

	/**
	 * Sets some default values for those parameters for the query when not set. WordPress's get_posts sets a few of
	 * these parameters true by default (compared to WP_Query), we should do the same.
	 * @internal
	 * @param \WP_Query $query
	 * @return \WP_Query
	 */
	public static function set_query_defaults( $query ) {
		if ( isset($query->query) && !isset($query->query['ignore_sticky_posts']) ) {
			$query->set('ignore_sticky_posts', true);
		}
		if ( isset($query->query) && !isset($query->query['suppress_filters']) ) {
			$query->set('suppress_filters', true);
		}
		if ( isset($query->query) && !isset($query->query['no_found_rows']) ) {
			$query->set('no_found_rows', true);
		}
		remove_filter('pre_get_posts', array('Timber\PostGetter', 'set_query_defaults'));
		return $query;
	}

	/**
	 * @param mixed $query
	 * @param string|array $PostClass
	 * @return PostCollection | QueryIterator
	 */
	public static function query_posts( $query = false, $PostClass = '\Timber\Post' ) {
		if ( $type = self::get_class_for_use_as_timber_post($query) ) {
			$PostClass = $type;
			if ( self::is_post_class_or_class_map($query) ) {
				$query = false;
			}
		}

		if ( is_object($query) && !is_a($query, 'WP_Query') ) {
			// The only object other than a query is a type of post object
			$query = array($query);
		}

		// @todo lift this into PostFactory
		if ( is_array($query) && count($query) && isset($query[0]) && is_object($query[0]) ) {
			// We have an array of post objects that already have data
			return new PostCollection($query, $PostClass);
		} else {
			// We have a query (of sorts) to work with
			$tqi = new QueryIterator($query, $PostClass);
			return $tqi;
		}
	}

	/**
	 * @return integer the ID of the post in the loop
	 */
	public static function loop_to_id() {
		if ( !self::wp_query_has_posts() ) { return false; }

		global $wp_query;
		$post_num = property_exists($wp_query, 'current_post')
				  ? $wp_query->current_post + 1
				  : 0
				  ;

		if ( !isset($wp_query->posts[$post_num]) ) { return false; }

		return $wp_query->posts[$post_num]->ID;
	}

	/**
	 * @return bool
	 */
	public static function wp_query_has_posts() {
		global $wp_query;
		return ($wp_query && property_exists($wp_query, 'posts') && $wp_query->posts);
	}

	/**
	 * @param string $post_type
	 * @param string|array $post_class
	 *
	 * @return string
	 */
	public static function get_post_class( $post_type, $post_class = '\Timber\Post' ) {
		/**
		 * Filters the class(es) used for different post types.
		 *
		 * @since 2.0.0
		 * @example
		 * ```
		 * // Use one class for all Timber posts.
		 * add_filter( 'timber/post/post_class', function( $post_class, $post_type ) {
		 *    return 'CustomPostClass';
		 * }, 10, 2 );
		 *
		 * // Use default class for all post types, except for pages.
		 * add_filter( 'timber/post/post_class', function( $post_class, $post_type ) {
		 *    // Bailout if not a page
		 *    if ( 'page' !== $post_type ) {
		 *        return $post_class;
		 *    }
		 *
		 *    return 'PagePost';
		 * }, 10, 2 );
		 *
		 * // Use a class map for different post types
		 * add_filter( 'timber/post/post_class', function( $post_class, $post_type ) {
		 *    return array(
		 *        'post' => 'BlogPost',
		 *        'apartment' => 'ApartmentPost',
		 *        'city' => 'CityPost',
		 *    );
		 * }, 10, 2 );
		 * ```
		 *
		 * @param string|array $post_class The post class(es) to use. Can be a string for a single
		 *                                 post class or an key-value array map to define which post
		 *                                 type should use which class. Default `Timber\Post`.
		 * @param string       $post_type  The post type of the post.
		 */
		$post_class = apply_filters( 'timber/post/post_class', $post_class, $post_type );

		/**
		 * Filters the class(es) used for different post types.
		 *
		 * @deprecated 2.0.0, use `timber/post/post_class` instead.
		 */
		$post_class = apply_filters_deprecated(
			'Timber\PostClassMap',
			array( $post_class ),
			'2.0.0',
			'timber/post/post_class'
		);

		$post_class_use = '\Timber\Post';

		if ( is_array($post_class) ) {
			if ( isset($post_class[$post_type]) ) {
				$post_class_use = $post_class[$post_type];
			}
		} elseif ( is_string($post_class) ) {
			$post_class_use = $post_class;
		} else {
			Helper::error_log('Unexpected value for PostClass: '.print_r($post_class, true));
		}

		if ( $post_class_use === '\Timber\Post' || $post_class_use === 'Timber\Post' ) {
			return $post_class_use;
		}

		if ( !class_exists($post_class_use) || !is_subclass_of($post_class_use, '\Timber\Post') ) {
			Helper::error_log('Class '.$post_class_use.' either does not exist or implement \Timber\Post');
			return '\Timber\Post';
		}

		return $post_class_use;
	}

	/**
	 * @param string|array $arg
	 * @return boolean
	 */
	public static function is_post_class_or_class_map( $arg ) {
		$maybe_type = self::get_class_for_use_as_timber_post($arg);
		if ( is_array($arg) && isset($arg['post_type']) ) {
			//the user has passed a true WP_Query-style query array that needs to be used later, so the $arg is not a class map or post class
			return false;
		}
		if ( $maybe_type ) {
			return true;
		}
		return false;
	}

	/**
	 * @param string|array $arg
	 * @return string|bool if a $type is found; false if not
	 */
	public static function get_class_for_use_as_timber_post( $arg ) {
		$type = false;

		if ( is_string($arg) ) {
			$type = $arg;
		} else if ( is_array($arg) && isset($arg['post_type']) ) {
			$type = $arg['post_type'];
		}

		if ( !$type ) {
			return false;
		}

		if ( !is_array($type) && class_exists($type) && is_subclass_of($type, '\Timber\Post') ) {
			return $type;
		}
	}
}
