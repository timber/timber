<?php

namespace Timber;

/**
 * Class PostsIterator
 */
class PostsIterator extends \ArrayIterator {
	public function current() {
		$post = parent::current();
        $post->setup( $this->key() );

        return $post;
	}
}
