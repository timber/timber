<?php

namespace Timber;

use Timber\PostsCollection;
use Timber\QueryIterator;

class PostGetter {

	/**
	 * @param mixed $query
	 * @param string $PostClass
	 * @return array|bool|null
	 */
	static function get_post( $query = false, $PostClass = '\Timber\Post' ) {
		$posts = self::get_posts($query, $PostClass);
		if ( $post = reset($posts) ) {
			return $post;
		}
	}

	static function get_posts( $query = false, $PostClass = '\Timber\Post', $return_collection = false ) {
		$posts = self::query_posts($query, $PostClass);
		return apply_filters('timber_post_getter_get_posts', $posts->get_posts($return_collection));
	}

	static function query_post( $query = false, $PostClass = '\Timber\Post' ) {
		$posts = self::query_posts($query, $PostClass);
		if ( method_exists($posts, 'current') && $post = $posts->current() ) {
			return $post;
		}
	}

	/**
	 * @param mixed $query
	 * @param string $PostClass
	 * @return array|bool|null
	 */
	static function query_posts( $query = false, $PostClass = '\Timber\Post' ) {
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
			return new PostsCollection($query, $PostClass);
		} else {
			// We have a query (of sorts) to work with
			$tqi = new QueryIterator($query, $PostClass);
			return $tqi;
		}
	}

	static function get_pids( $query ) {
		$posts = self::get_posts($query);
		$pids = array();
		foreach ( $posts as $post ) {
			if ( isset($post->ID) ) {
				$pids[] = $post->ID;
			}
		}
		return $pids;
	}

	static function loop_to_id() {
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
	static function wp_query_has_posts() {
		global $wp_query;
		return ($wp_query && property_exists($wp_query, 'posts') && $wp_query->posts);
	}

	/**
	 * @param string|array $arg
	 * @return bool
	 */
	static function is_post_class_or_class_map( $arg ) {
		$maybe_type = self::get_class_for_use_as_timber_post($arg);
		if ( is_array($arg) && isset($arg['post_type']) ) {
			//the user has passed a true WP_Query-style query array that needs to be used later, so the $arg is not a class map or post class
			return false;
		}
		if ( $maybe_type ) {
			return true;
		}
	}

	/**
	 * @param string|array $arg
	 * @return string|bool if a $type is found; false if not
	 */
	static function get_class_for_use_as_timber_post( $arg ) {
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
