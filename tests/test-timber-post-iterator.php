<?php

class TestTimberPostIterator extends Timber_UnitTestCase {
	private $collector;

	/**
	 * Checks if the 'loop_end' hook runs after last array iteration.
	 */
	function testLoopEndAfterLastItem() {
		$pids = $this->factory->post->create_many(3);
		$posts = new Timber\PostQuery( $pids );
		$this->collector = [];

		add_action( 'loop_end', array( $this, 'loop_end' ) );

		foreach ( $posts as $post ) {
			$this->collector[] = $post->title;
		}

		$this->assertCount( 4, $this->collector );
		$this->assertEquals( 'loop_end', $this->collector[3] );
	}

	/**
	 * Checks if wp_reset_postdata() is run after a query.
	 */
	function testResetPostDataAfterLastItem() {
		$pids = $this->factory->post->create_many(3);
		$posts = new Timber\PostQuery( $pids );

		// Make sure $wp_query is set up.
		$this->go_to( get_permalink( get_option( 'page_for_posts' ) ) );

		// Save initial post for later check.
		global $post;
		$initial_post = $post;

		foreach ( $posts as $post ) {
			// Run something
			$post->title;
		}

		$this->assertEquals( $initial_post, $post );
	}

	public function loop_end() {
		$this->collector[] = 'loop_end';
	}
}
