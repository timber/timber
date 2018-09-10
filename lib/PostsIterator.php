<?php

namespace Timber;

/**
 * Class PostsIterator
 */
class PostsIterator extends \ArrayIterator {
	/**
	 * Prepares the state before working on a post.
	 *
	 * Calls the setup function of the current post to setup post data.
	 * Before starting the loop, it will call the 'loop_start' hook to improve compatibility.
	 *
	 * @return mixed
	 */
	public function current() {
		// Fire action when the loop has just started.
		if ( 0 === $this->key() ) {
			do_action_ref_array( 'loop_start', array( &$GLOBALS['wp_query'] ) );
			global $wp_query;
			var_dump('In the loop');
			var_dump($wp_query->in_the_loop);
		}

		$post = parent::current();
		$post->setup();

		return $post;
	}

	/**
	 * Cleans up state before advancing to next item.
	 *
	 * In the last run of a loop through posts, it will call the 'loop_end' hook to improve
	 * compatibility.
	 *
	 * @since 2.0.0
	 */
	public function next() {
		$post = $this->current();
		$post->teardown();

		// Fire action when the loop has ended.
		if ( $this->key() === $this->count() - 1 ) {
			do_action_ref_array( 'loop_end', array( &$GLOBALS['wp_query'] ) );
			wp_reset_postdata();
		}

		parent::next();
	}
}
