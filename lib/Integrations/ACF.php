<?php
/**
 * Integration with Advanced Custom Fields (ACF)
 *
 * @package Timber
 */

namespace Timber\Integrations;

/**
 * Class used to handle integration with Advanced Custom Fields
 */
class ACF {

	public function __construct() {
		add_filter('timber_post_get_meta', array( $this, 'post_get_meta' ), 10, 2);
		add_filter('timber_post_get_meta_field', array( $this, 'post_get_meta_field' ), 10, 3);
		add_filter('timber/post/meta_object_field', array( $this, 'post_meta_object' ), 10, 3);
		add_filter('timber/term/meta', array( $this, 'term_get_meta' ), 10, 3);
		add_filter('timber/term/meta/field', array( $this, 'term_get_meta_field' ), 10, 4);
		add_filter('timber_user_get_meta_field_pre', array( $this, 'user_get_meta_field' ), 10, 3);
		add_filter('timber/term/meta/set', array( $this, 'term_set_meta' ), 10, 4);
	}

	public function post_get_meta( $customs, $post_id ) {
		return $customs;
	}

	public function post_get_meta_field( $value, $post_id, $field_name ) {
		return get_field($field_name, $post_id);
	}

	public function post_meta_object( $value, $post_id, $field_name ) {
		return get_field_object($field_name, $post_id);
	}

	public function term_get_meta_field( $value, $term_id, $field_name, $term ) {
		$searcher = $term->taxonomy . '_' . $term->ID;
		return get_field($field_name, $searcher);
	}

	public function term_set_meta( $value, $field, $term_id, $term ) {
		$searcher = $term->taxonomy . '_' . $term->ID;
		update_field($field, $value, $searcher);
		return $value;
	}

	public function term_get_meta( $fields, $term_id, $term ) {
		$searcher = $term->taxonomy . '_' . $term->ID; // save to a specific category.
		$fds      = get_fields($searcher);
		if ( is_array($fds) ) {
			foreach ( $fds as $key => $value ) {
				$key            = preg_replace('/_/', '', $key, 1);
				$key            = str_replace($searcher, '', $key);
				$key            = preg_replace('/_/', '', $key, 1);
				$field          = get_field($key, $searcher);
				$fields[ $key ] = $field;
			}
			$fields = array_merge($fields, $fds);
		}
		return $fields;
	}

	public function user_get_meta( $fields, $user_id ) {
		return $fields;
	}

	public function user_get_meta_field( $value, $uid, $field ) {
		return get_field($field, 'user_' . $uid);
	}
}
