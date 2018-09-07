<?php
namespace Timber;
/**
 * Class PostsIterator
 */
class PostsIterator extends \ArrayIterator {
	/**
	 * Calls the setup function of the current post
	 * to setup post data
	 *
	 * @return mixed
	 */
	public function current() {
		// Fire action when the loop has just started.
		if ( 0 === $this->key() ) {
			do_action_ref_array( 'loop_start', array( &$GLOBALS['wp_query'] ) );
		}
		$post = parent::current();
		$post->setup();
		return $post;
	}
	public function next()
	{
		$post = $this->current();
		$post->teardown();
		// Fire action when the loop has ended.
		if ( $this->count() === $this->key() ) {
			do_action_ref_array( 'loop_end', array( &$GLOBALS['wp_query'] ) );
			wp_reset_postdata();
		}
		parent::next();
	}
}
