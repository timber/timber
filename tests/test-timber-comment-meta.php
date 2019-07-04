<?php

use Timber\Comment;

/**
 * Class TestTimberCommentMeta
 */
class TestTimberCommentMeta extends Timber_UnitTestCase {
	/**
	 * Function hit helper.
	 *
	 * @var bool
	 */
	protected $is_get_comment_meta_hit;

	function testPreGetMetaValuesDisableFetch() {
		$this->is_get_comment_meta_hit = false;

		add_filter( 'timber/comment/pre_get_meta_values', '__return_false' );
		add_filter( 'get_comment_metadata', function( $value, $object_id, $meta_key ) {
			if ( empty( $meta_key ) ) {
				$this->is_get_comment_meta_hit = true;
			}

			return $value;
		}, 10, 3 );

		$comment_id = $this->factory->comment->create();
		$comment    = new Comment( $comment_id );

		$this->assertEquals( false, $this->is_get_comment_meta_hit );

		remove_filter( 'timber/comment/pre_get_meta_values', '__return_false' );
	}

	function testPreGetMetaValuesCustomFetch() {
		$callable = function( $comment_meta, $pid, $post ) {
			$key = 'critical_value';

			return [
				$key => get_comment_meta( $pid, $key ),
			];
		};

		add_filter( 'timber/comment/pre_get_meta_values', $callable, 10, 3 );

		$comment_id = $this->factory->comment->create();

		update_comment_meta( $comment_id, 'hidden_value', 'super-big-secret' );
		update_comment_meta( $comment_id, 'critical_value', 'I am needed, all the time' );

		$comment = new Comment( $comment_id );

		$this->assertEquals( 'super-big-secret', $comment->meta( 'hidden_value' ) );
		$this->assertEquals(
			$comment->meta( 'critical_value' ),
			'I am needed, all the time'
		);

		remove_filter( 'timber/comment/pre_get_meta_values', $callable );
	}
}
