<?php

class TimberStaticHomeTest extends WP_UnitTestCase {

	function testPageAsPostsPage() {
		$pids = $this->factory->post->create_many(6);
		$page_id = $this->factory->post->create(array('post_type' => 'page'));
		update_option('page_for_posts', $page_id);
		$this->go_to(home_url('/?p='.$page_id));
		global $wp_query;
		$wp_query->queried_object_id = $page_id;
		$page = new TimberPost();
		$this->assertEquals($page_id, $page->ID);
	}

	function testPageAsStaticFront() {

	}

}
