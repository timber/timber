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

		function testShowUnmoderatedCommentIfByLoggedInUser() {
			$post_id = $this->factory->post->create();
			$uid = $this->factory->user->create();
			wp_set_current_user( $uid );
			$quote = "You know, I always wanted to pretend I was an architect";
			$comment_id = $this->factory->comment->create(array('comment_post_ID' => $post_id, 'comment_content' => $quote, 'user_id' => $uid, 'comment_approved' => 0));
			$post = new TimberPost($post_id);
			$this->assertEquals(1, count($post->comments()));
			wp_set_current_user( 0 );
		}

		function testShowUnmoderatedCommentIfByCurrentUser() {
			$post_id = $this->factory->post->create();
			add_filter('wp_get_current_commenter', function($author_data) {
				$author_data['comment_author_email'] = 'jarednova@upstatement.com';
				return $author_data;
			});
			$commenter = wp_get_current_commenter();
			$quote = "And in that moment, I was a marine biologist";
			$comment_id = $this->factory->comment->create(array('comment_post_ID' => $post_id, 'comment_content' => $quote,'comment_approved' => 0, 'comment_author_email' => 'jarednova@upstatement.com'));
			$post = new TimberPost($post_id);
			$this->assertEquals(1, count($post->comments()));
		}

		function testMultilevelThreadedComments() {
			$post_id = $this->factory->post->create(array('post_title' => 'Gobbles'));
			$comment_id = $this->factory->comment->create(array('comment_post_ID' => $post_id));
			$comment_child_id = $this->factory->comment->create(array('comment_post_ID' => $post_id, 'comment_parent' => $comment_id));
			$comment_grandchild_id = $this->factory->comment->create(array('comment_post_ID' => $post_id, 'comment_parent' => $comment_child_id));
			$post = new TimberPost($post_id);
			$comments = $post->get_comments();
			$this->assertEquals(1, count($comments));
			$children = $comments[0]->children();
			$this->assertEquals(2, count($children));
		}

		function testMultilevelThreadedCommentsCorrectParents(){
			$post_id = $this->factory->post->create(array('post_title' => 'Gobbles'));
			$comment_id = $this->factory->comment->create(array('comment_post_ID' => $post_id));
			$comment2_id = $this->factory->comment->create(array('comment_post_ID' => $post_id));
			$comment2_child_id = $this->factory->comment->create(array('comment_post_ID' => $post_id, 'comment_parent' => $comment2_id));
			$comment2_grandchild_id = $this->factory->comment->create(array('comment_post_ID' => $post_id, 'comment_parent' => $comment2_child_id));
			$post = new TimberPost($post_id);
			$comments = $post->get_comments();
			$children = $comments[1]->children();
			$this->assertEquals($comment2_id, $children[0]->comment_parent);
			$grandchild = $children[1];
			$this->assertEquals($comment2_child_id, $grandchild->comment_parent);
		}

	}
