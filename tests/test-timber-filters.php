<?php

	class TestTimberFilters extends WP_UnitTestCase {

		function testPostMetaFieldFilter(){
			$post_id = $this->factory->post->create();
			update_post_meta($post_id, 'Frank', 'Drebin');
			$tp = new TimberPost($post_id);
			//add_filter('timber_post_get_meta_field', array($this, 'filter_timber_post_get_meta_field'), 10, 4);
			//$this->assertEquals('Drebin', $tp->meta('Frank'));
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

		function testUserMetaFilter(){
			$uid = $this->factory->user->create();
			$user = new TimberUser($uid);
			$user->update('jared', 'novack');
			add_filter('timber_user_get_meta_field', array($this, 'filter_timber_user_get_meta_field'), 10, 4);
			$this->assertEquals($user->meta('jared'), 'novack');
		}

		function filter_timber_user_get_meta_field($value, $uid, $field_name, $timber_user){
			$this->assertEquals($field_name, 'jared');
			$this->assertEquals($value, 'novack');
			$this->assertEquals($timber_user->ID, $uid);
			return $value;
		}

		function testTermMetaFilter(){
			$tid = $this->factory->term->create();
			$term = new TimberTerm($tid);
			add_filter('timber_term_get_meta_field', array($this, 'filter_timber_term_get_meta_field'), 10, 4);
			$term->meta("panic!");
		}

		function filter_timber_term_get_meta_field($value, $tid, $field_name, $timber_term){
			$this->assertEquals($tid, $timber_term->ID);
			$this->assertEquals($field_name, 'panic!');
			return $value;
		}

	}