<?php

class TestTimberPostGetter extends WP_UnitTestCase {

	function testQueryPost() {
		$posts = $this->factory->post->create_many( 6 );
		$post = Timber::get_post($posts[3]);
		$this->assertNotEquals(get_the_ID(), $post->ID);
		$post = Timber::query_post($posts[3]);
		$this->assertEquals(get_the_ID(), $post->ID);
	}

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

	function testGetPostsFromArray(){
		$pids = $this->factory->post->create_many( 15 );
		$posts = Timber::get_posts($pids);
		$this->assertEquals(15, count($posts));
		$this->assertEquals($pids[3], $posts[3]->ID);
	}

	function testGetPostsFromSlug() {
		$post = $this->factory->post->create( array( 'post_name' => 'silly-post' ) );
		$posts = Timber::get_posts_from_slug( 'silly-post' );
		$this->assertEquals( 1, count( $posts ) );
		$this->assertEquals( 'silly-post', $posts[0]->slug );
	}

}
