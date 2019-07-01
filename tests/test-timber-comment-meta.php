<?php

use Timber\Comment;

/**
 * Class TestTimberCommentMeta
 */
class TestTimberCommentMeta extends Timber_UnitTestCase {
	function testPreGetMetaValuesDisableFetch() {
		add_filter( 'timber/comment/pre_get_meta_values', '__return_false' );

		$comment = $this->factory->comment->create();

		update_user_meta( $comment, 'hidden_value', 'Super secret value' );

		$comment = new Comment( $comment );

		$this->assertEmpty( $comment->meta( 'hidden_value' ) );

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
