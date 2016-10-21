<?php

namespace Timber\Factory;

/**
 * Class ObjectFactory
 * @package Timber
 *
 * The ObjectFactory is a developer's way of converting WordPress objects into Timber ones!
 * Passed a WordPress object or identifier, it will return Timber's core, corresponding object class by default.
 * However, the ObjectFactory allows a developer to override this default with their own extension of the Timber objects.
 *
 */
class Factory {

	/**
	 * @param \WP_Post|int|null $post_identifier
	 *
	 * @return \Timber\Post|null
	 */
	public static function get_post( $post_identifier = null, $class_default = null ) {
		$post = Post::get_object( $post_identifier );

		$class = ObjectClassFactory::get_class( 'post', get_post_type( $post ), $post, $class_default );

		return $post ? new $class( $post ) : null;
	}


	/**
	 * @param \WP_Term|int|string|null $term
	 *
	 *
	 * @return \Timber\Term
	 */
	public static function get_term( $term = null, $taxonomy = 'category', $field = 'term_taxonomy_id', $class_default = null ) {
		$term = Term::get_object( $term, $taxonomy, $field );

		$class = ObjectClassFactory::get_class( 'term', $term->taxonomy, $term, $class_default );

		return $term ? new $class( $term ) : null;
	}
}