<?php

use Timber\Post;

/**
 * Class TestTimberPostMeta
 */
class TestTimberPostMeta extends Timber_UnitTestCase {
	function testPreGetMetaValuesCustomFetch() {
		$callable = function( $meta, $post_id, $field_name ) {
			if ( 'critical_value' === $field_name ) {
				return 'I’m an updated critical value.';
			}

			return $meta;
		};

		add_filter( 'timber/post/pre_meta', $callable, 10, 3 );

		$post_id = $this->factory->post->create();

		update_post_meta( $post_id, 'hidden_value', 'super-big-secret' );
		update_post_meta( $post_id, 'critical_value', 'I am needed, all the time' );

		$post = new Post( $post_id );
		$this->assertEquals( 'super-big-secret', $post->raw_meta( 'hidden_value' ) );
		$this->assertEquals(
			'I am needed, all the time',
			$post->raw_meta( 'critical_value' )
		);
		$this->assertEquals(
			'I’m an updated critical value.',
			$post->meta( 'critical_value' )
		);

		remove_filter( 'timber/post/pre_meta', $callable );
	}

	/**
	 * Meta values still need to fetchable through raw_meta() even when the pre_meta filter is used.
	 */
	function testPreGetMetaValuesDisableFetchMetaValues(){
		add_filter( 'timber/post/pre_meta', '__return_false' );

		$post_id = $this->factory->post->create();

		update_post_meta( $post_id, 'meta_value', 'I am a meta value' );

		$post = new Post( $post_id );

		$raw_value = $post->raw_meta( 'meta_value' );
		$value     = $post->meta( 'meta_value' );

		$this->assertEquals( 'I am a meta value', $raw_value );
		$this->assertEquals( false, $value );

		remove_filter( 'timber/post/pre_meta', '__return_false' );
	}
}
