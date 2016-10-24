<?php

namespace Timber\Factory;

/**
 * Class Term
 * @package Timber\Factory
 */
class TermFactory extends Factory implements FactoryInterface {


	/**
	 * @param \WP_Term|int|string|null $term $term
	 * @param string $taxonomy
	 * @param string $field
	 *
	 * @return \Timber\Term
	 */
	public static function get( $term = null, $taxonomy = 'category', $field = 'term_taxonomy_id' ) {
		return ( new self() )->get_object( $term, $taxonomy, $field );
	}

	/**
	 * @param \WP_Term|int|string|null $term
	 *
	 *
	 * @return \Timber\Term
	 */
	public function get_object( $term = null, $taxonomy = 'category', $field = 'term_taxonomy_id' ) {
		$term = TermGetter::get_object( $term, $taxonomy, $field );

		$class = ObjectClassFactory::get_class( 'term', $term->taxonomy, $term, $this->object_class );

		return $term ? new $class( $term ) : null;
	}

}