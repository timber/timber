<?php

namespace Timber;

use Timber\Term;
use Timber\Helper;

class TermGetter {

	/**
	 * @param string|array $args
	 * @param array $maybe_args
	 * @param string $TermClass
	 * @return mixed
	 */
	public static function get_terms( $args = null, $maybe_args = array(), $TermClass = 'Term' ) {
		if ( is_string($maybe_args) && !strstr($maybe_args, '=') ) {
			//the user is sending the $TermClass in the second argument
			$TermClass = $maybe_args;
		}
		if ( is_string($maybe_args) && strstr($maybe_args, '=') ) {
			parse_str($maybe_args, $maybe_args);
		}
		if ( is_string($args) && strstr($args, '=') ) {
			//a string and a query string!
			$parsed = self::get_term_query_from_query_string($args);
			if ( is_array($maybe_args) ) {
				$parsed->args = array_merge($parsed->args, $maybe_args);
			}
			return self::handle_term_query($parsed->taxonomies, $parsed->args, $TermClass);
		} else if ( is_string($args) ) {
			//its just a string with a single taxonomy
			$parsed = self::get_term_query_from_string($args);
			if ( is_array($maybe_args) ) {
				$parsed->args = array_merge($parsed->args, $maybe_args);
			}
			return self::handle_term_query($parsed->taxonomies, $parsed->args, $TermClass);
		} else if ( is_array($args) && Helper::is_array_assoc($args) ) {
			//its an associative array, like a good ole query
			$parsed = self::get_term_query_from_assoc_array($args);
			return self::handle_term_query($parsed->taxonomies, $parsed->args, $TermClass);
		} else if ( is_array($args) ) {
			//its just an array of strings or IDs (hopefully)
			$parsed = self::get_term_query_from_array($args);
			if ( is_array($maybe_args) ) {
				$parsed->args = array_merge($parsed->args, $maybe_args);
			}
			return self::handle_term_query($parsed->taxonomies, $parsed->args, $TermClass);
		} else if ( is_null($args) ) {
			return self::handle_term_query(get_taxonomies(), array(), $TermClass);
		}
		return null;
	}

	/**
	 * @param string|array $taxonomies
	 * @param string|array $args
	 * @param string $TermClass
	 * @return mixed
	 */
	public static function handle_term_query( $taxonomies, $args, $TermClass ) {
		if ( !isset($args['hide_empty']) ) {
			$args['hide_empty'] = false;
		}
		if ( isset($args['term_id']) && is_int($args['term_id']) ) {
			$args['term_id'] = array($args['term_id']);
		}
		if ( isset($args['term_id']) ) {
			$args['include'] = $args['term_id'];
		}
		$terms = get_terms($taxonomies, $args);
		foreach ( $terms as &$term ) {
			$term = new $TermClass($term->term_id, $term->taxonomy);
		}
		return $terms;
	}

	/**
	 * @param string $query_string
	 * @return stdClass
	 */
	protected static function get_term_query_from_query_string( $query_string ) {
		$args = array();
		parse_str($query_string, $args);
		$ret = self::get_term_query_from_assoc_array($args);
		return $ret;
	}

	/**
	 * @param string $taxs
	 * @return stdClass
	 */
	protected static function get_term_query_from_string( $taxs ) {
		$ret = new \stdClass();
		$ret->args = array();
		if ( is_string($taxs) ) {
			$taxs = array($taxs);
		}
		$ret->taxonomies = self::correct_taxonomy_names($taxs);
		return $ret;
	}

	/**
	 * @param array $args
	 * @return stdClass
	 */
	public static function get_term_query_from_assoc_array( $args ) {
		$ret = new \stdClass();
		$ret->args = $args;
		if ( isset($ret->args['tax']) ) {
			$ret->taxonomies = $ret->args['tax'];
		} else if ( isset($ret->args['taxonomies']) ) {
			$ret->taxonomies = $ret->args['taxonomies'];
		} else if ( isset($ret->args['taxs']) ) {
			$ret->taxonomies = $ret->args['taxs'];
		} else if ( isset($ret->args['taxonomy']) ) {
			$ret->taxonomies = $ret->args['taxonomy'];
		}
		if ( isset($ret->taxonomies) ) {
			if ( is_string($ret->taxonomies) ) {
				$ret->taxonomies = array($ret->taxonomies);
			}
			$ret->taxonomies = self::correct_taxonomy_names($ret->taxonomies);
		} else {
			$ret->taxonomies = get_taxonomies();
		}
		return $ret;
	}

	/**
	 * @param array $args
	 * @return stdClass
	 */
	public static function get_term_query_from_array( $args ) {
		if ( is_array($args) && !empty($args) ) {
			//okay its an array with content
			if ( is_int($args[0]) ) {
				return self::get_term_query_from_array_of_ids($args);
			} else if ( is_string($args[0]) ) {
				return self::get_term_query_from_array_of_strings($args);
			}
		}
		return null;
	}

	/**
	 * @param integer[] $args
	 * @return stdClass
	 */
	public static function get_term_query_from_array_of_ids( $args ) {
		$ret = new \stdClass();
		$ret->taxonomies = get_taxonomies();
		$ret->args['include'] = $args;
		return $ret;
	}

	/**
	 * @param string[] $args
	 * @return stdClass
	 */
	public static function get_term_query_from_array_of_strings( $args ) {
		$ret = new \stdClass();
		$ret->taxonomies = self::correct_taxonomy_names($args);
		$ret->args = array();
		return $ret;
	}

	/**
	 * @param string|array $taxs
	 * @return array
	 */
	private static function correct_taxonomy_names( $taxs ) {
		if ( is_string($taxs) ) {
			$taxs = array($taxs);
		}
		foreach ( $taxs as &$tax ) {
			if ( $tax == 'tags' || $tax == 'tag' ) {
				$tax = 'post_tag';
			} else if ( $tax == 'categories' ) {
				$tax = 'category';
			}
		}
		return $taxs;
	}

}
