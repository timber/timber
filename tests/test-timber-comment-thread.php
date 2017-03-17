<?php

	class TestTimberCommentThread extends Timber_UnitTestCase {

		function testCommentThreadWithArgs() {
			$post_id = $this->factory->post->create(array('post_title' => 'Gobbles'));
			$comment_id_array = $this->factory->comment->create_many( 5, array('comment_post_ID' => $post_id) );
			$args = array();
			$ct = new Timber\CommentThread($post_id, $args);
			$this->assertEquals( 5, count($ct) );
		}

	}