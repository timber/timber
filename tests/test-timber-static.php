<?php

class TestTimberStaticPages extends Timber_UnitTestCase {

	function tearDown() {
		update_option('show_on_front', 'posts');
		update_option('page_on_front', '0');
		update_option('page_for_posts', '0');
	}

	function testPageAsPostsPage() {
		$pids = $this->factory->post->create_many(6);
		$page_id = $this->factory->post->create(array('post_type' => 'page'));
		update_option('page_for_posts', $page_id);
		$this->go_to(home_url('/?page_id='.$page_id));
		$page = new TimberPost();
		$this->assertEquals($page_id, $page->ID);
	}

	function testPageAsJustAPage() {
		$pids = $this->factory->post->create_many(6);
		$page_id = $this->factory->post->create(array('post_title' => 'Foobar', 'post_name' => 'foobar', 'post_type' => 'page'));
		$this->go_to(home_url('/?page_id='.$page_id));
		$page = new TimberPost();
		$this->assertEquals($page_id, $page->ID);
	}

	function testPageAsStaticFront() {
		$pids = $this->factory->post->create_many(6);
		$page_id = $this->factory->post->create(array('post_type' => 'page'));
		update_option('page_on_front', $page_id);
		$this->go_to(home_url('/'));
		global $wp_query;
		$wp_query->queried_object_id = $page_id;
		$page = new TimberPost();
		$this->assertEquals($page_id, $page->ID);
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

	function testStaticPostPage() {
		$this->clearPosts();
		$page_id = $this->factory->post->create(array('post_title' => 'Gobbles', 'post_type' => 'page'));
		update_option('page_for_posts', $page_id);
		$this->go_to(home_url('/?p='.$page_id));
		$children = $this->factory->post->create_many(10, array('post_title' => 'Timmy'));
		$posts = Timber::get_posts();
		$first_post = $posts[0];
		$this->assertEquals('Timmy', $first_post->title());
	}

	function testOtherPostOnStaticPostPage() {
		$page_id = $this->factory->post->create(array('post_title' => 'Gobbles', 'post_type' => 'page'));
		update_option('page_for_posts', $page_id);
		$post_id = $this->factory->post->create(array('post_title' => 'My Real post', 'post_type' => 'post'));
		$this->go_to(home_url('/?p='.$page_id));
		$post = new TimberPost($post_id);
		$this->assertEquals($post_id, $post->ID);
		$page = new TimberPost();
		$this->assertEquals($page_id, $page->ID);
	}

		function testRegularStaticPage() {
			$page_id = $this->factory->post->create(array('post_title' => 'Mister Slave', 'post_type' => 'page'));
			$children = $this->factory->post->create_many(10, array('post_title' => 'Timmy'));
			$this->go_to(home_url('/?p='.$page_id));
			$posts = Timber::get_posts();
			$this->assertEquals(0, count($posts));
			$page = new TimberPost();
			$this->assertEquals($page_id, $page->ID);
		}

		function testRegularStaticPageFlipped() {
			$page_id = $this->factory->post->create(array('post_title' => 'Mister Slave', 'post_type' => 'page'));
			$children = $this->factory->post->create_many(10, array('post_title' => 'Timmy'));
			$this->go_to(home_url('/?p='.$page_id));
			$page = new TimberPost();
			$this->assertEquals($page_id, $page->ID);
			$posts = Timber::get_posts();
			$this->assertEquals(0, count($posts));
		}

}
