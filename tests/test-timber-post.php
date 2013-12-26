<?php

	class TimberPostTest extends WP_UnitTestCase {

		function testPost(){
			$post_id = $this->factory->post->create();
			$post = new TimberPost($post_id);
			$this->assertEquals('TimberPost', get_class($post));
			$this->assertEquals($post_id, $post->ID);
		}

		function testNext(){
			$posts = array();
			for($i = 0; $i<2; $i++){
				$posts[] = $this->factory->post->create();
				sleep(1);
			}
			$firstPost = new TimberPost($posts[0]);
			$nextPost = new TimberPost($posts[1]);
			$this->assertEquals($firstPost->next()->ID, $nextPost->ID);
		}

		function testNextWithDraftAndFallover(){
			$posts = array();
			for($i = 0; $i<3; $i++){
				$posts[] = $this->factory->post->create();
				sleep(1);
			}
			$firstPost = new TimberPost($posts[0]);
			$nextPost = new TimberPost($posts[1]);
			$nextPostAfter = new TimberPost($posts[2]);
			$nextPost->post_status = 'draft';
			wp_update_post($nextPost);
			$this->assertEquals($firstPost->next()->ID, $nextPostAfter->ID);
		}

		function testNextWithDraft(){
			$posts = array();
			for($i = 0; $i<2; $i++){
				$posts[] = $this->factory->post->create();
				sleep(1);
			}
			$firstPost = new TimberPost($posts[0]);
			$nextPost = new TimberPost($posts[1]);
			$nextPost->post_status = 'draft';
			wp_update_post($nextPost);
			$nextPostTest = $firstPost->next();
			print_r($nextPostTest);
		}

		function testPostInitObject(){
			$post_id = $this->factory->post->create();
			$post = get_post($post_id);
			$post = new TimberPost($post);
			$this->assertEquals($post->ID, $post_id);
		}

		function testPostByName(){
			$post_id = $this->factory->post->create();
			$post = new TimberPost($post_id);
			$pid_from_name = TimberPost::get_post_id_by_name($post->post_name);
			$this->assertEquals($pid_from_name, $post_id);
		}

		function testUpdate(){
			$post_id = $this->factory->post->create();
			$post = new TimberPost($post_id);
			$rand = rand_str();
			$post->update('test_meta', $rand);
			$post = new TimberPost($post_id);
			$this->assertEquals($rand, $post->test_meta);
		}

		function testGetPreview() {

		}
	}