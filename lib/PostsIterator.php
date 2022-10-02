<?php

namespace Timber;

/**
 * Class PostsIterator
 *
 * @package Timber
 */
class PostsIterator extends \ArrayIterator {

	#[\ReturnTypeWillChange]
	public function current() {
		global $post;
		$post = parent::current();
		return $post;
	}

}
