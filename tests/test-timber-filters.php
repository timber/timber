<?php

	class TestTimberFilters extends WP_UnitTestCase {

		function testPostMetaFieldFilter(){
			$post_id = $this->factory->post->create();
			update_post_meta($post_id, 'Frank', 'Drebin');
			$tp = new TimberPost($post_id);
			add_filter('timber_post_get_meta_field', function( $value, $pid, $field_name, $timber_post) use ($post_id){
				$this->assertEquals($field_name, 'Frank');
				$this->assertEquals($pid, $post_id);
				$this->assertEquals($timber_post->ID, $post_id);
			});
			$this->assertEquals('Drebin', $tp->meta('Frank'));
		}

		function testCommentMetaFilter(){
			$post_id = $this->factory->post->create();
			$comment_id = $this->factory->comment->create(array('comment_post_ID' => $post_id));
			$comment = new TimberComment($comment_id);
			$comment->update('ghost', 'busters');
			add_filter('timber_comment_get_meta_field', function($value, $cid, $field_name, $timber_comment) use ($comment_id){
				$this->assertEquals($cid, $comment_id);
				$this->assertEquals($field_name, 'ghost');
				$this->assertEquals($value, 'busters');
				$this->assertEquals($comment_id, $timber_comment->ID);
			});
			$this->assertEqulas($comment->meta('ghost'), 'busters');
		}

	}