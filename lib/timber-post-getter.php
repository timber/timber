<?php

class TimberPostGetter {

	/**
	 * @param mixed $query
	 * @param string $PostClass
	 * @return array|bool|null
	 */
	static function get_post($query = false, $PostClass = 'TimberPost') {
		$posts = self::get_posts( $query, $PostClass );
		if ( $post = reset($posts ) ) {
			return $post;
		}
	}

	static function get_posts( $query = false, $PostClass = 'TimberPost', $return_collection = false ) {
		$posts = self::query_posts( $query, $PostClass );
		return apply_filters('timber_post_getter_get_posts', $posts->get_posts( $return_collection ));
	}

	static function query_post( $query = false, $PostClass = 'TimberPost' ) {
		$posts = self::query_posts( $query, $PostClass );
		if ( $post = $posts->current() ) {
			return $post;
		}
	}

	/**
	 * @param mixed $query
	 * @param string $PostClass
	 * @return array|bool|null
	 */
	static function query_posts($query = false, $PostClass = 'TimberPost' ) {
		if (self::is_post_class_or_class_map($query)) {
			$PostClass = $query;
			$query = false;
		}

		if (is_object($query) && !is_a($query, 'WP_Query') ){
			// The only object other than a query is a type of post object
			$query = array( $query );
		}

		if ( is_array( $query ) && count( $query ) && isset( $query[0] ) && is_object( $query[0] ) ) {
			// We have an array of post objects that already have data
			return new TimberPostsCollection( $query, $PostClass );
		} else {
			// We have a query (of sorts) to work with
			$tqi = new TimberQueryIterator( $query, $PostClass );
			return $tqi;
		}
	}

	static function get_pids($query){
		$posts = self::get_posts($query);
		$pids = array();
		foreach($posts as $post){
			if (isset($post->ID)){
				$pids[] = $post->ID;
			}
		}
		return $pids;
	}

	static function loop_to_id() {
		if (!self::wp_query_has_posts()) { return false; }

		global $wp_query;
		$post_num = property_exists($wp_query, 'current_post')
				  ? $wp_query->current_post + 1
				  : 0
				  ;

		if (!isset($wp_query->posts[$post_num])) { return false; }

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
	static function is_post_class_or_class_map($arg){
		if (is_string($arg) && class_exists($arg)) {
			return true;
		}
		if (is_array($arg)) {
			foreach ($arg as $item) {
				if (is_string($item) && class_exists($item)) {
					return true;
				}
			}
		}
	}
}
