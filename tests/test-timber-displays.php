<?php

	class TimberDisplaysTest extends WP_UnitTestCase {

		function tearDown() {
			update_option('show_on_front', 'posts');
			update_option('page_on_front', '0');
			update_option('page_for_posts', '0');
		}

		function testFrontPageAsPage() {
			$spaceballs = "What's the matter, Colonel Sandurz? Chicken?";
			$page_id = $this->factory->post->create(array('post_title' => 'Spaceballs', 'post_content' => $spaceballs, 'post_type' => 'page'));
			update_option('show_on_front', 'page');
			update_option('page_on_front', $page_id);
			$this->go_to(home_url('/'));
			$post = new TimberPost();
			$this->assertEquals($page_id, $post->ID);
		}

		function testSpecialPostPage() {
			$page_id = $this->factory->post->create(array('post_title' => 'Gobbles', 'post_type' => 'page'));
			update_option('page_for_posts', $page_id);
			$this->go_to(home_url('/?p='.$page_id));
			$children = $this->factory->post->create_many(10, array('post_title' => 'Timmy'));
			$posts = Timber::get_posts();
			$first_post = $posts[0];
			$this->assertEquals('Timmy', $first_post->title());
		}
	}
