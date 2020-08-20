<?php

namespace Timber;

use WP_Post;

use Timber\Factory\PostFactory;

/**
 * Trait implementing ArrayAccess::getOffset() using lazy instantiation.
 *
 * @see /docs/v2/guides/posts.md#laziness-and-caching
 * @internal
 */
trait AccessesPostsLazily {
	/**
	 * PostFactory instance used internally to instantiate Posts.
	 *
	 * @var \Timber\Factory\PostFactory
	 */
	private $factory;

	/**
	 * Lazily instantiates Timber\Post instances from WP_Post objects.
	 *
	 * @internal
	 */
	public function offsetGet($offset) {
		$post = parent::offsetGet($offset);
		if ($post instanceof WP_Post) {
			$post = $this->factory()->from($post);
			$this->offsetSet($offset, $post);
		}

		return $post;
	}

	/**
	 * @internal
	 */
	private function factory() : PostFactory {
		if (!$this->factory) {
			$this->factory = new PostFactory();
		}

		return $this->factory;
  }

}