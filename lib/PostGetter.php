<?php

namespace Timber;

use Timber\PostCollection;
use Timber\QueryIterator;

class PostGetter {

	/**
	 * @param mixed        $query
	 * @param string|array $PostClass
	 * @return \Timber\Post|bool
	 */
	public static function get_post( $query = false, $PostClass = '\Timber\Post' ) {
		// if a post id is passed, grab the post directly
		if ( is_numeric($query) ) {
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

		if ( $post = reset($posts) ) {
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
		$posts = self::query_posts($query, $PostClass);
		return apply_filters('timber_post_getter_get_posts', $posts->get_posts($return_collection));
	}

	public static function query_post( $query = false, $PostClass = '\Timber\Post' ) {
		$posts = self::query_posts($query, $PostClass);
		if ( method_exists($posts, 'current') && $post = $posts->current() ) {
			return $post;
		}
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
		$post_class = apply_filters('Timber\PostClassMap', $post_class);
		$post_class_use = '\Timber\Post';

		if ( is_array($post_class) ) {
			if ( isset($post_class[$post_type]) ) {
				$post_class_use = $post_class[$post_type];
			} else {
				Helper::error_log($post_type.' not found in '.print_r($post_class, true));
			}
		} elseif ( is_string($post_class) ) {
			$post_class_use = $post_class;
		} else {
			Helper::error_log('Unexpeted value for PostClass: '.print_r($post_class, true));
		}

		if ( $post_class_use === '\Timber\Post' || $post_class_use === 'Timber\Post') {
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
