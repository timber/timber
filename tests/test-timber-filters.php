<?php

	class TestTimberFilters extends WP_UnitTestCase {

		function testPostMetaFieldFilter(){
			$post_id = $this->factory->post->create();
			update_post_meta($post_id, 'Frank', 'Drebin');
			$tp = new TimberPost($post_id);
			add_filter('timber_post_get_meta_field', array($this, 'filter_timber_post_get_meta_field'), 10, 4);
			$this->assertEquals('Drebin', $tp->meta('Frank'));
		}

		function filter_timber_post_get_meta_field($value, $pid, $field_name, $timber_post){
			$this->assertEquals($field_name, 'Frank');
			$this->assertEquals($value, 'Drebin');
			$this->assertEquals($timber_post->ID, $pid);
			return $value;
		}

		function testCommentMetaFilter(){
			$post_id = $this->factory->post->create();
			$comment_id = $this->factory->comment->create(array('comment_post_ID' => $post_id));
			$comment = new TimberComment($comment_id);
			$comment->update('ghost', 'busters');
			add_filter('timber_comment_get_meta_field', array($this, 'filter_timber_comment_get_meta_field'), 10, 4);
			$this->assertEquals($comment->meta('ghost'), 'busters');
		}

		function filter_timber_comment_get_meta_field($value, $cid, $field_name, $timber_comment){
			$this->assertEquals($field_name, 'ghost');
			$this->assertEquals($value, 'busters');
			$this->assertEquals($cid, $timber_comment->ID);
			return $value;
		}

	}