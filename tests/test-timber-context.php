<?php

use Timber\Post;
use Timber\PostQuery;
use Timber\Term;
use Timber\Timber;

/**
 * @group posts-api
 * @group post-collections
 */
class TestTimberContext extends Timber_UnitTestCase {
	/**
	 * This throws an infite loop if memorization isn't working
	 */
	function testContextLoop() {
		$this->add_filter_temporarily( 'timber/context', function( $context ) {
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
		$post    = Timber::get_post( $post_id );

		$this->assertArrayNotHasKey( 'posts', $context );
		$this->assertEquals( $post, $context['post'] );

		$context = Timber::context();
		$this->assertEquals('http://example.org', $context['http_host']);
	}

	function testPostsContextSimple() {
		update_option( 'show_on_front', 'posts' );
		$this->factory->post->create_many( 3 );
		$this->go_to( '/' );

		$context = Timber::context();

		$this->assertArrayNotHasKey( 'post', $context );
		$this->assertInstanceOf( PostQuery::class, $context['posts'] );
		$this->assertCount( 3, $context['posts'] );
	}

	function testPostsContextSearch() {
		$this->factory->post->create_many( 3, [
			'post_content' => 'here are some things',
			'post_status'  => 'publish',
	  ]	);
		$this->factory->post->create_many( 3, [
			'post_content' => 'here is some stuff',
			'post_status'  => 'publish',
	  ]	);
		query_posts('s=stuff');

		$context = Timber::context();

		$this->assertArrayNotHasKey( 'post', $context );
		$this->assertInstanceOf( PostQuery::class, $context['posts'] );
		$this->assertCount( 3, $context['posts'] );
		$this->assertEquals( 'stuff', $context['search_query'] );
	}

	function testPostsContextCategory() {
		$stuff = wp_insert_term('Stuff', 'category');
		$cat_posts = $this->factory->post->create_many( 3, [
			'post_status'  => 'publish',
	  ]	);
		foreach ($cat_posts as $id) {
			wp_set_object_terms($id, $stuff, 'category');
		}

		// 3 uncategorized posts
		$this->factory->post->create_many( 3, [
			'post_status'  => 'publish',
	  ]	);

		query_posts('cat=' . $stuff['term_id']);

		$context = Timber::context();

		$this->assertArrayNotHasKey( 'post', $context );
		$this->assertInstanceOf( PostQuery::class, $context['posts'] );
		$this->assertCount( 3, $context['posts'] );

		$this->assertInstanceOf( Term::class, $context['term'] );
		$this->assertEquals( 'Stuff', $context['term']->title() );
	}

	function testPostsContextTag() {
		$stuff = wp_insert_term('Stuff', 'post_tag');
		$cat_posts = $this->factory->post->create_many( 3, [
			'post_status'  => 'publish',
	  ]	);
		foreach ($cat_posts as $id) {
			wp_set_object_terms($id, $stuff, 'post_tag');
		}

		// 3 untagged posts
		$this->factory->post->create_many( 3, [
			'post_status'  => 'publish',
	  ]	);

		query_posts('tag=stuff');

		$context = Timber::context();

		$this->assertArrayNotHasKey( 'post', $context );
		$this->assertInstanceOf( PostQuery::class, $context['posts'] );
		$this->assertCount( 3, $context['posts'] );

		$this->assertInstanceOf( Term::class, $context['term'] );
		$this->assertEquals( 'Stuff', $context['term']->title() );
	}

	function testPostsContextTax() {
		register_taxonomy('thingy', ['post'], [
			'public' => true,
		]);
		$stuff = wp_insert_term('Stuff', 'thingy');
		$cat_posts = $this->factory->post->create_many( 3, [
			'post_status'  => 'publish',
	  ]	);
		foreach ($cat_posts as $id) {
			wp_set_object_terms($id, $stuff, 'thingy');
		}

		// 3 non-thingy posts
		$this->factory->post->create_many( 3, [
			'post_status'  => 'publish',
	  ]	);

		query_posts([
			'tax_query' => [
				[
					'taxonomy' => 'thingy',
					'terms'    => [$stuff['term_id']],
					'field'    => 'term_id',
				],
			],
		]);

		$context = Timber::context();

		$this->assertArrayNotHasKey( 'post', $context );
		$this->assertInstanceOf( PostQuery::class, $context['posts'] );
		$this->assertCount( 3, $context['posts'] );

		$this->assertInstanceOf( Term::class, $context['term'] );
		$this->assertEquals( 'Stuff', $context['term']->title() );
	}

	function testIfSetupFunctionIsRunInSingularTemplates() {
		$post_id = $this->factory->post->create();
		$this->go_to( get_permalink( $post_id ) );

		global $wp_query;

		$this->assertFalse( $wp_query->in_the_loop );

		Timber::context();

		$this->assertTrue( $wp_query->in_the_loop );
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

  function testContext() {
		$context = Timber::context();
		$this->assertEquals('http://example.org', $context['http_host']);
	}

}
