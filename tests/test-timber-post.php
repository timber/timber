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

		function testNextCategory(){
			$posts = array();
			for($i = 0; $i<3; $i++){
				$posts[] = $this->factory->post->create();
				sleep(1);
			}
			wp_set_object_terms($posts[0], 'TestMe', 'category', false);
			wp_set_object_terms($posts[2], 'TestMe', 'category', false);
			$firstPost = new TimberPost($posts[0]);
			$nextPost = new TimberPost($posts[2]);
			$this->assertEquals($firstPost->next('category')->ID, $nextPost->ID);
		}

		function testPrev(){
			$posts = array();
			for($i = 0; $i<2; $i++){
				$posts[] = $this->factory->post->create();
				sleep(1);
			}
			$lastPost = new TimberPost($posts[1]);
			$prevPost = new TimberPost($posts[0]);
			$this->assertEquals($lastPost->prev()->ID, $prevPost->ID);
		}

		function testPrevCategory(){
			$posts = array();
			for($i = 0; $i<3; $i++){
				$posts[] = $this->factory->post->create();
				sleep(1);
			}
			wp_set_object_terms($posts[0], 'TestMe', 'category', false);
			wp_set_object_terms($posts[2], 'TestMe', 'category', false);
			$lastPost = new TimberPost($posts[2]);
			$prevPost = new TimberPost($posts[0]);
			$this->assertEquals($lastPost->prev('category')->ID, $prevPost->ID);
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

		function testDoubleEllipsis(){
			$post_id = $this->factory->post->create();
			$post = new TimberPost($post_id);
			$post->post_excerpt = 'this is super dooper trooper long words';
			$prev = $post->get_preview(3, true);
			$this->assertEquals(1, substr_count($prev, '&hellip;'));
		}

		function testGetPreview() {
			$post_id = $this->factory->post->create();
			$post = new TimberPost($post_id);

			// no excerpt
			$post->post_excerpt = '';
			$post->post_content = 'this is super dooper trooper long words';
			$preview = $post->get_preview(3);
			$this->assertRegExp('/this is super &hellip;  <a href="http:\/\/example.org\/\?p=\d+" class="read-more">Read More<\/a>/', $preview);

			// excerpt set, force is false, no read more
			$post->post_excerpt = 'this is excerpt longer than three words';
			$preview = $post->get_preview(3, false, '');
			$this->assertEquals($preview, $post->post_excerpt);

			// custom read more set
			$post->post_excerpt = '';
			$preview = $post->get_preview(3, false, 'Custom more');
			$this->assertRegExp('/this is super &hellip;  <a href="http:\/\/example.org\/\?p=\d+" class="read-more">Custom more<\/a>/', $preview);

			// content with <!--more--> tag, force false
			$post->post_content = 'this is super dooper<!--more--> trooper long words';
			$preview = $post->get_preview(2, false, '');
			$this->assertEquals($preview, 'this is super dooper');
		}

		function testTitle(){
			$title = 'Fifteen Million Merits';
			$post_id = $this->factory->post->create();
			$post = new TimberPost($post_id);
			$post->post_title = $title;
			wp_update_post($post);
			$this->assertEquals($title, trim(strip_tags($post->title())));
			$this->assertEquals($title, trim(strip_tags($post->get_title())));
		}

		function testContent(){
			$quote = 'The way to do well is to do well.';
			$post_id = $this->factory->post->create();
			$post = new TimberPost($post_id);
			$post->post_content = $quote;
			wp_update_post($post);
			$this->assertEquals($quote, trim(strip_tags($post->content())));
			$this->assertEquals($quote, trim(strip_tags($post->get_content())));
		}

		function testMetaCustomArrayFilter(){
			add_filter('timber_post_get_meta', function($customs){
				foreach($customs as $key=>$value){
					$flat_key = str_replace('-', '_', $key);
					$flat_key .= '_flat';
					$customs[$flat_key] = $value;
				}
				// print_r($customs);
				return $customs;
			});
			$post_id = $this->factory->post->create();
			update_post_meta($post_id, 'the-field-name', 'the-value');
			update_post_meta($post_id, 'with_underscores', 'the_value');
			$post = new TimberPost($post_id);
			$this->assertEquals($post->with_underscores_flat, 'the_value');
			$this->assertEquals($post->the_field_name_flat, 'the-value');
		}

	}