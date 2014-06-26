<?php

class TestTimberPostGetter extends WP_UnitTestCase {

	function testGetPostsInLoop() {
		$posts = $this->factory->post->create_many( 55 );
		$this->go_to( '/' );
		$start = microtime( true );
		if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();
				$posts = Timber::get_posts();
			}
		}
		$end = microtime( true );
		$diff = $end - $start;
		//if this takes more than 10 seconds, we're in trouble
		$this->assertLessThan( 10, $diff );
	}

	function testGetPostsFromLoop() {
		require_once 'php/timber-post-subclass.php';
		$posts = $this->factory->post->create_many( 15 );
		$this->go_to( '/' );
		$posts = Timber::get_posts();
		$this->assertEquals( 10, count( $posts ) );
		$posts = Timber::get_posts_from_loop( 'TimberPostSubclass' );
		$this->assertEquals( 10, count( $posts ) );
		$this->assertEquals( 'TimberPostSubclass', get_class( $posts[0] ) );
	}

	function testGetPostsFromSlug() {
		$post = $this->factory->post->create( array( 'post_name' => 'silly-post' ) );
		$posts = Timber::get_posts_from_slug( 'silly-post' );
		$this->assertEquals( 1, count( $posts ) );
		$this->assertEquals( 'silly-post', $posts[0]->slug );
	}

}
