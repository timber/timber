<?php

namespace Timber\Factory;

/**
 * Class Term
 * @package Timber\Factory
 */
class Term implements ObjectFactoryInterface {

	/**
	 * @param int|string $identifier
	 * @param string $taxonomy
	 *
	 * @return \WP_Term|null
	 */
	public static function get_object( $identifier = null, $taxonomy = 'category', $field = 'term_taxonomy_id' ) {

		if ( is_object( $identifier ) ) {
			return get_term( $identifier );
		}

		if ( $identifier === null ) {
			$identifier = static::get_term_from_query();
		}

		if ( ! is_numeric( $identifier ) && 'term_taxonomy_id' === $field ) {
			$field = 'slug';
		}

		return get_term_by( $field, $identifier, $taxonomy ) ?: null;
	}

	/**
	 * @internal
	 * @return integer
	 */
	protected static function get_term_from_query() {
		global $wp_query;
		if ( isset( $wp_query->queried_object ) ) {
			$qo = $wp_query->queried_object;
			if ( isset( $qo->term_id ) ) {
				return $qo->term_id;
			}
		}
		if ( isset( $wp_query->tax_query->queries[ 0 ][ 'terms' ][ 0 ] ) ) {
			return $wp_query->tax_query->queries[ 0 ][ 'terms' ][ 0 ];
		}

		return null;
	}

}