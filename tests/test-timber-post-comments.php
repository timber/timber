<?php

	class TestTimberPostComments extends Timber_UnitTestCase {

		function testComments() {
			$post_id = $this->factory->post->create(array('post_title' => 'Gobbles'));
			$comment_id_array = $this->factory->comment->create_many( 5, array('comment_post_ID' => $post_id) );
			$post = new TimberPost($post_id);
			$this->assertEquals( 5, count($post->comments()) );
			$this->assertEquals( 5, $post->get_comment_count() );
		}

		function testCommentCount() {
			$post_id = $this->factory->post->create(array('post_title' => 'Gobbles'));
			$comment_id_array = $this->factory->comment->create_many( 5, array('comment_post_ID' => $post_id) );
			$post = new TimberPost($post_id);
			$this->assertEquals( 2, count($post->comments(2)) );
			$this->assertEquals( 5, $post->get_comment_count() );
		}

		function testCommentCountZero() {
			$quote = 'Named must your fear be before banish it you can.';
            $post_id = $this->factory->post->create(array('post_content' => $quote));
            $post = Timber::get_post($post_id);
            $this->assertEquals(0, $post->get_comment_count());
		}

	}
