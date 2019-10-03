<?php

namespace Timber\Factory;

use Timber\CoreInterface;
use Timber\Comment;

use WP_Comment;

/**
 * Internal API class for instantiating Comments
 */
class CommentFactory {
	public function from($params) {
		if (is_int($params)) {
			return $this->from_id($params);
		}

		// @todo deal with assoc array queries
		if (is_array($params)) {
			return array_map([$this, 'build'], $params);
		}
	}

	protected function from_id(int $id) {
		return $this->build(get_comment($id));
	}

	protected function get_comment_class(WP_Comment $comment) {
		// Get the user-configured Class Map
		$map = apply_filters( 'timber/comment/classmap', []);

		$type  = get_post_type($comment->comment_post_ID);
		$class = $map[$type] ?? null;

		if (is_callable($class)) {
			$class = $class($comment);
		}

		// If we don't have a Comment class by now, fallback on the default class
		return $class ?? Comment::class;
	}

	protected function build(WP_Comment $comment) : CoreInterface {
		$class = $this->get_comment_class($comment);

    // @todo make Core constructors protected, call Comment::build() here
		return new $class($comment);
	}
}
