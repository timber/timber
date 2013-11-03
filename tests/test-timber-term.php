<?php

	class TimberTermTest extends WP_UnitTestCase {

		function testTerm(){
			$term_id = $this->factory->term->create();
			$term = new TimberTerm($term_id);
			$this->assertEquals('TimberTerm', get_class($term));
		}

		function testTermInitObject(){
			$term_id = $this->factory->term->create();
			$term = get_term($term_id, 'post_tag');
			$term = new TimberTerm($term);
			$this->assertEquals($term->ID, $term_id);
		}

		function testTermLink(){
			$term_id = $this->factory->term->create();
			$term = new TimberTerm($term_id);
			$this->assertContains('http://', $term->link());
			$this->assertContains('http://', $term->get_link());
		}

		function testTermPath(){
			$term_id = $this->factory->term->create();
			$term = new TimberTerm($term_id);
			$this->assertFalse(strstr($term->path(), 'http://'));
			$this->assertFalse(strstr($term->get_path(), 'http://'));
		}

		function testGetPostsOld(){
			$term_id = $this->factory->term->create();
			$posts = array();
			$posts[] = $this->factory->post->create();
			$posts[] = $this->factory->post->create();
			$posts[] = $this->factory->post->create();
			foreach($posts as $post_id){
				wp_set_object_terms($post_id, $term_id, 'post_tag', true);
			}
			$term = new TimberTerm($term_id);
			$gotten_posts = $term->get_posts();
			$this->assertEquals(count($posts), count($gotten_posts));
		}

		function testGetPostsAsPageOld(){
			$term_id = $this->factory->term->create();
			$posts = array();
			$posts[] = $this->factory->post->create();
			$posts[] = $this->factory->post->create();
			$posts[] = $this->factory->post->create();
			foreach($posts as $post_id){
				set_post_type($post_id, 'page');
				wp_set_object_terms($post_id, $term_id, 'post_tag', true);
			}
			$term = new TimberTerm($term_id);
			$gotten_posts = $term->get_posts(count($posts), 'page');
			$this->assertEquals(count($posts), count($gotten_posts));
			$gotten_posts = $term->get_posts(count($posts), 'any');
			$this->assertEquals(count($posts), count($gotten_posts));
			$gotten_posts = $term->get_posts(count($posts), 'post');
			$this->assertEquals(0, count($gotten_posts));
		}

		function testGetPostsNew(){
			require_once('php/timber-post-subclass.php');
			$term_id = $this->factory->term->create();
			$posts = array();
			$posts[] = $this->factory->post->create();
			$posts[] = $this->factory->post->create();
			$posts[] = $this->factory->post->create();
			foreach($posts as $post_id){
				set_post_type($post_id, 'page');
				wp_set_object_terms($post_id, $term_id, 'post_tag', true);
			}
			$term = new TimberTerm($term_id);
			$gotten_posts = $term->get_posts('post_type=page');
			$this->assertEquals(count($posts), count($gotten_posts));
			$gotten_posts = $term->get_posts('post_type=page', 'TimberPostSubclass');
			$this->assertEquals(count($posts), count($gotten_posts));
			$this->assertEquals($gotten_posts[0]->foo(), 'bar');
			$gotten_posts = $term->get_posts(array('post_type' => 'page'), 'TimberPostSubclass');
			$this->assertEquals($gotten_posts[0]->foo(), 'bar');
			$this->assertEquals(count($posts), count($gotten_posts));
		}

	}