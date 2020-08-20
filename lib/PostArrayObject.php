<?php

namespace Timber;

use JsonSerializable;

/**
 * PostArrayObject class for dealing with arbitrary collections of Posts
 * (typically not wrapping a `WP_Query` directly, which is what `Timber\PostQuery` does).
 *
 * @api
 */
class PostArrayObject extends \ArrayObject implements PostCollectionInterface, JsonSerializable {
	use AccessesPostsLazily;

	public function __construct(array $posts) {
		parent::__construct($posts, 0, PostsIterator::class);
	}

	/**
	 * @inheritdoc
	 */
	public function pagination(array $options = []) {
		return null;
	}

	/**
	 * Returns realized (eagerly instantiated) Timber\Post data to serialize to JSON.
	 *
	 * @internal
	 */
	public function jsonSerialize() {
		return $this->getArrayCopy();
	}
}