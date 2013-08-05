<?php

class TimberTest extends WP_UnitTestCase {

	function testSample() {
		// replace this with some actual testing code
		$this->assertTrue( true );
	}

	function testGetPostNumeric(){
		$post_id = $this->factory->post->create();
		$post = Timber::get_post($post_id);
		$this->assertEquals('TimberPost', get_class($post));
	}

	function testGetPostString(){
		$this->factory->post->create();
		$post = Timber::get_post('post_type=post');
		$this->assertEquals('TimberPost', get_class($post));
	}

	function testGetPostsQueryString(){
		$this->factory->post->create();
		$this->factory->post->create();
		$posts = Timber::get_posts('post_type=post');
		$this->assertGreaterThan(1, count($posts));
	}
	
	function testGetPostsQueryStringClassName(){
		$this->factory->post->create();
		$this->factory->post->create();
		$posts = Timber::get_posts('post_type=post');
		$post = $posts[0];
		$this->assertEquals('TimberPost', get_class($post));
	}
}

