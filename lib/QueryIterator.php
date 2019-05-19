<?php

namespace Timber;

use Timber\Helper;
use Timber\PostCollection;

// Exit if accessed directly
if ( !defined('ABSPATH') ) {
	exit;
}

class QueryIterator implements \Iterator, \Countable {

	/**
	 *
	 *
	 * @var WP_Query
	 */
	private $_query = null;
	private $_posts_class = 'Timber\Post';

	public function __construct( $query = false, $posts_class = 'Timber\Post' ) {
		add_action('pre_get_posts', array($this, 'fix_number_posts_wp_quirk'));
		add_action('pre_get_posts', array($this, 'fix_cat_wp_quirk'));
		
		if ( $posts_class ) {
			$this->_posts_class = $posts_class;
		}

		if ( is_a($query, 'WP_Query') ) {
			// We got a full-fledged WP Query, look no further!
			$the_query = $query;
		} elseif ( false === $query ) {
			// If query is explicitly set to false, use the main loop
			global $wp_query;
			$the_query = & $wp_query;
			//if we're on a custom posts page?
			$the_query = self::handle_maybe_custom_posts_page($the_query);
		} elseif ( Helper::is_array_assoc($query) || (is_string($query) && strstr($query, '=')) ) {
			// We have a regularly formed WP query string or array to use
			$the_query = new \WP_Query($query);
		} elseif ( is_numeric($query) || is_string($query) ) {
			// We have what could be a post name or post ID to pull out
			$the_query = self::get_query_from_string($query);
		} elseif ( is_array($query) && count($query) && (is_integer($query[0]) || is_string($query[0])) ) {
			// We have a list of pids (post IDs) to extract from
			$the_query = self::get_query_from_array_of_ids($query);
		} elseif ( is_array($query) && empty($query) ) {
			// it's an empty array
			$the_query = array();
		} else {
			Helper::error_log('I have failed you! in '.basename(__FILE__).'::'.__LINE__);
			Helper::error_log($query);
			// We have failed hard, at least let get something.
			$the_query = new \WP_Query();
		}

		$this->_query = $the_query;

	}

	public function post_count() {
		return $this->_query->post_count;
	}

	public function get_pagination( $prefs ) {
		return new Pagination($prefs, $this->_query);
	}

	public function get_posts( $return_collection = false ) {
		if ( isset($this->_query->posts) ) {
			$posts = new PostCollection($this->_query->posts, $this->_posts_class);
			return ($return_collection) ? $posts : $posts->get_posts();
		}
	}

	//
	// GET POSTS
	//
	public static function get_query_from_array_of_ids( $query = array() ) {
		if ( !is_array($query) || !count($query) ) {
			return null;
		}

		return new \WP_Query(array(
				'post_type'=> 'any',
				'ignore_sticky_posts' => true,
				'post__in' => $query,
				'orderby'  => 'post__in',
				'nopaging' => true
			));
	}

	public static function get_query_from_string( $string = '' ) {
		$post_type = false;

		if ( is_string($string) && strstr($string, '#') ) {
			//we have a post_type directive here
			list($post_type, $string) = explode('#', $string);
		}

		$query = array(
			'post_type' => ($post_type) ? $post_type : 'any'
		);

		if ( is_numeric($string) ) {
			$query['p'] = $string;

		} else {
			$query['name'] = $string;
		}

		return new \WP_Query($query);
	}

	//
	// Iterator Interface
	//

	public function valid() {
		return $this->_query->have_posts();
	}

	public function current() {
		global $post;

		$this->_query->the_post();

		// Sets up the global post, but also return the post, for use in Twig template
		$posts_class = $this->_posts_class;
		return new $posts_class($post);
	}

	/**
	 * Don't implement next, because current already advances the loop
	 */
	final public function next() {}

	public function rewind() {
		$this->_query->rewind_posts();
	}

	public function key() {
		$this->_query->current_post;
	}

	//get_posts users numberposts
	public static function fix_number_posts_wp_quirk( $query ) {
		if ( isset($query->query) && isset($query->query['numberposts'])
				&& !isset($query->query['posts_per_page']) ) {
			$query->set('posts_per_page', $query->query['numberposts']);
		}
		return $query;
	}

	//get_posts uses category, WP_Query uses cat. Why? who knows...
	public static function fix_cat_wp_quirk( $query ) {
		if ( isset($query->query) && isset($query->query['category'])
				&& !isset($query->query['cat']) ) {
			$query->set('cat', $query->query['category']);
			unset($query->query['category']);
		}
		return $query;
	}

	/**
	 * this will test for whether a custom page to display posts is active, and if so, set the query to the default
	 * @param  WP_Query $query the original query recived from WordPress
	 * @return WP_Query
	 */
	public static function handle_maybe_custom_posts_page( $query ) {
		if ( $custom_posts_page = get_option('page_for_posts') ) {
			if ( isset($query->query['p']) && $query->query['p'] == $custom_posts_page ) {
				return new \WP_Query(array('post_type' => 'post'));
			}
		}
		return $query;
	}

	/**
	 * Count elements of an object.
	 *
	 * Necessary for some Twig `loop` variable properties.
	 * @see http://twig.sensiolabs.org/doc/tags/for.html#the-loop-variable
	 * @link  http://php.net/manual/en/countable.count.php
	 * @return int The custom count as an integer.
	 * The return value is cast to an integer.
	 */
	public function count() {
		return $this->post_count();
	}
}
