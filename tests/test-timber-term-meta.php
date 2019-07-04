<?php

use Timber\Term;

/**
 * Class TestTimberTermMeta
 */
class TestTimberTermMeta extends Timber_UnitTestCase {
	/**
	 * Function hit helper.
	 *
	 * @var bool
	 */
	protected $is_get_term_meta_hit;

	function testPreGetMetaValuesDisableFetch() {
		$this->is_get_term_meta_hit = false;

		add_filter( 'timber/term/pre_get_meta_values', '__return_false' );
		add_filter( 'get_term_metadata', function( $value, $object_id, $meta_key ) {
			if ( empty( $meta_key ) ) {
				$this->is_get_term_meta_hit = true;
			}

			return $value;
		}, 10, 3 );
		$term_id = $this->factory->term->create();
		$term    = new Term( $term_id );

		$this->assertEquals( false, $this->is_get_term_meta_hit );

		remove_filter( 'timber/term/pre_get_meta_values', '__return_false' );
	}

	function testPreGetMetaValuesCustomFetch(){
		$callable = function( $term_meta, $pid, $post ) {
			$key = 'critical_value';

			return [
				$key => get_term_meta( $pid, $key ),
			];
		};

		add_filter( 'timber/term/pre_get_meta_values', $callable , 10, 3 );

		$term_id = $this->factory->term->create();

		update_term_meta( $term_id, 'hidden_value', 'super-big-secret' );
		update_term_meta( $term_id, 'critical_value', 'I am needed, all the time' );

		$term = new Term( $term_id );

		$this->assertEquals( 'super-big-secret', $term->meta( 'hidden_value' ) );
		$this->assertEquals(
			'I am needed, all the time',
			$term->meta( 'critical_value' )
		);

		remove_filter( 'timber/term/pre_get_meta_values', $callable );
	}
}
