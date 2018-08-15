<?php

class TestTimberContext extends Timber_UnitTestCase {
	/**
	 * This throws an infite loop if memorization isn't working
	 */
	function testContextLoop() {
		add_filter( 'timber/context', function( $context ) {
			$context          = Timber::context();
			$context['zebra'] = 'silly horse';

			return $context;
		} );

		$context = Timber::context();

		$this->assertEquals( 'http://example.org', $context['http_host'] );
	}

	function testPostContextSimple() {
		$post_id = $this->factory->post->create();

		$this->go_to( get_permalink( $post_id ) );

		$context = Timber::context();
		$post    = new Timber\Post( $post_id );

		$this->assertEquals( $post, $context['post'] );
	}

	function testPostContextWithID() {
		$post_id1 = $this->factory->post->create();
		$post_id2 = $this->factory->post->create();

		$this->go_to( get_permalink( $post_id1 ) );

		$context = Timber::context( array(
			'post' => $post_id2,
		) );

		$this->assertEquals( $post_id2, $context['post']->ID );
	}

	function testPostContextWithExtendedPost() {
		require_once(__DIR__.'/php/timber-post-subclass.php');

		$post_id = $this->factory->post->create();
		$this->go_to( get_permalink( $post_id ) );

		$context = Timber::context( array(
			'post' => new TimberPostSubclass(),
		) );

		$this->assertInstanceOf( 'TimberPostSubclass', $context['post'] );
	}

	function testPostsContextSimple() {
		update_option( 'show_on_front', 'posts' );
		$this->factory->post->create_many( 3 );
		$this->go_to( '/' );

		$context = Timber::context();

		$this->assertInstanceOf( 'Timber\PostQuery', $context['posts'] );
		$this->assertCount( 3, $context['posts']->get_posts() );
	}

	function testChangeDefaultQueryArgument() {
		update_option( 'show_on_front', 'posts' );
		$this->factory->post->create_many( 3, array( 'post_type' => 'page' ) );
		$this->go_to( '/' );

		$context = Timber::context( array(
			'posts' => array(
				'post_type' => 'page',
		        'posts_per_page' => 2,
			),
		) );

		$this->assertInstanceOf( 'Timber\PostQuery', $context['posts'] );
		$this->assertCount( 2, $context['posts']->get_posts() );
		$this->assertEquals( 'page', $context['posts'][0]->post_type );
	}

	function testCancelDefaultQueryPosts() {
		update_option( 'show_on_front', 'posts' );
		$this->factory->post->create_many( 3 );
		$this->go_to( '/' );

		$context = Timber::context( array(
			'cancel_default_query' => true
		) );

		$this->assertArrayNotHasKey( 'posts', $context );
	}

	function testCancelDefaultQueryPostsWithPostArgs() {
		update_option( 'show_on_front', 'posts' );
		$this->factory->post->create_many( 3 );

		$this->go_to( '/' );

		$context = Timber::context( array(
			'cancel_default_query' => true,
			'posts' => array(
				'posts_per_page' => 2,
			),
		) );

		$this->assertArrayHasKey( 'posts', $context );
		$this->assertCount( 2, $context['posts']->get_posts() );
	}

	function testDisableDefaultQueryByArgsPost() {
		$post_id = $this->factory->post->create();
		$this->go_to( get_permalink( $post_id ) );

		$context = Timber::context( array(
		    'post'  => false,
		) );

		$this->assertArrayNotHasKey( 'post', $context );
	}

	function testDisableDefaultQueryByArgsPosts() {
		update_option( 'show_on_front', 'posts' );
		$this->factory->post->create_many( 3 );
		$this->go_to( '/' );

		$context = Timber::context( array(
		    'posts'  => false,
		) );

		$this->assertArrayNotHasKey( 'posts', $context );
	}

	function testDisableDefaultQueryByFilterPost() {
		add_filter( 'timber/context/args', function( $args ) {
			$args['post'] = false;

			return $args;
		} );

		$post_id = $this->factory->post->create();
		$this->go_to( get_permalink( $post_id ) );

		$context = Timber::context();

		$this->assertArrayNotHasKey( 'post', $context );
	}

	function testDisableDefaultQueryByFilterPosts() {
		add_filter( 'timber/context/args', function( $args ) {
			$args['posts'] = false;

			return $args;
		} );

		$this->factory->post->create_many( 3 );
		$this->go_to( get_post_type_archive_link( 'post' ) );

		$context = Timber::context();

		$this->assertArrayNotHasKey( 'posts', $context );
	}

	function testIfInTheLoopIsSetToTrueInSingularTemplates() {
		$post_id = $this->factory->post->create();
		$this->go_to( get_permalink( $post_id ) );

		global $wp_query;

		$this->assertFalse( $wp_query->in_the_loop );

		Timber::context();

		$this->assertTrue( $wp_query->in_the_loop );
	}

	function testLoopStartHookInSingularTemplates() {
		add_action( 'loop_start', function( $wp_query ) {
			$wp_query->touched_loop_start = true;
		} );

		$post_id = $this->factory->post->create();
		$this->go_to( get_permalink( $post_id ) );
		Timber::context();

		global $wp_query;
		$this->assertTrue( $wp_query->touched_loop_start );
	}

	/**
	 * Tests whether 'the_post' action is called when a singular template is displayed.
	 *
	 * @see TestTimberPost::testPostConstructorAndThePostHook()
	 */
	function testIfThePostHookIsRunInSingularTemplates() {
		add_action( 'the_post', function( $post ) {
			add_filter( 'touched_the_post_action', '__return_true' );
		} );

		$post_id = $this->factory()->post->create();
		$this->go_to( get_permalink( $post_id ) );

		Timber::context();

		$this->assertTrue( apply_filters( 'touched_the_post_action', false ) );
	}

}
