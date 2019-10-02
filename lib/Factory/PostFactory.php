<?php

namespace Timber\Factory;

use Timber\CoreInterface;
use Timber\Post;

use WP_Post;

/**
 * Internal API class for instantiating posts
 */
class PostFactory {
  public function get_post(int $id) {
    return $this->build(get_post($id));
  }

	public function from($queryOrPosts) {
		// @todo maybe return PostCollection/PostQuery/QueryIterator here?
		return $this->from_posts_array($queryOrPosts);
	}

	protected function from_posts_array(array $posts) : array {
		return array_map([$this, 'build'], $posts);
	}

	protected function get_post_class(WP_Post $post) : string {
    // Get the user-configured Class Map
    $map = apply_filters( 'timber/post/classmap', [
      'post'       => Post::class,
      'page'       => Post::class,
      // @todo special logic for attachments?
      'attachment' => Attachment::class,
    ] );

		$class = $map[$post->post_type] ?? null;

    // If class is a callable, call it to get the actual class name
    if (is_callable($class)) {
      $class = $class($post);
    }

    // If we don't have a post class by now, fallback on the default class
    return $class ?? Post::class;
	}

  protected function build(WP_Post $post) : CoreInterface {
		$class = $this->get_post_class($post);

    // @todo make Core constructors protected, call Post::build() here
    return new $class($post);
  }
}
