<?php

use Timber\Post;

/**
 * Class TestTimberPostMeta
 */
class TestTimberPostMeta extends Timber_UnitTestCase {
	function testPreGetMetaValuesDisableFetch(){
		add_filter( 'timber/post/pre_get_meta_values', '__return_false' );

		$post_id = $this->factory->post->create();

		update_post_meta( $post_id, 'hidden_value', 'Super secret value' );

		$post = new Post( $post_id );

		$this->assertEquals( null, $post->raw_meta( 'hidden_value' ) );

		remove_filter( 'timber/post/pre_get_meta_values', '__return_false' );
	}

	function testPreGetMetaValuesCustomFetch(){
		$callable = function( $customs, $pid, $post ) {
			$key = 'critical_value';

			return [
				$key => get_post_meta( $pid, $key ),
			];
		};

		add_filter( 'timber/post/pre_get_meta_values', $callable , 10, 3 );

		$post_id = $this->factory->post->create();

		update_post_meta( $post_id, 'hidden_value', 'super-big-secret' );
		update_post_meta( $post_id, 'critical_value', 'I am needed, all the time' );

		$post = new Post( $post_id );
		$this->assertEquals( null, $post->raw_meta( 'hidden_value' ) );
		$this->assertEquals(
			'I am needed, all the time',
			$post->raw_meta( 'critical_value' )
		);

		remove_filter( 'timber/post/pre_get_meta_values', $callable );
	}

	/**
	 * This seems like an incredible edge case test from 1.x
	 * @ignore since 2.0
	 */
	/*
	function testMetaCustomArrayFilter(){
		add_filter('timber_post_get_meta', function($customs) {
			error_log('RUN FITER');
			print_r($customs);
			foreach( $customs as $key=>$value ){
				$flat_key = str_replace('-', '_', $key);
				$flat_key .= '_flat';
				$customs[$flat_key] = $value;
			}
			// print_r($customs);
			return $customs;
		});
		$post_id = $this->factory->post->create();
		update_post_meta($post_id, 'the-field-name', 'the-value');
		update_post_meta($post_id, 'with_underscores', 'the_value');
		$post = new Timber\Post($post_id);
		$this->assertEquals($post->with_underscores_flat, 'the_value');
		//$this->assertEquals($post->the_field_name_flat, 'the-value');
	}*/
}
