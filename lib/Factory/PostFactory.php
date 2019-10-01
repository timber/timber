<?php

namespace Timber\Factory;

use Timber\CoreInterface;
use Timber\Post;

use WP_Post;

/**
 * Internal API class for instantiating posts
 */
class PostFactory {
  public function get(int $id) {
    return $this->build(get_post($id));
  }

  public function build(WP_Post $post) : CoreInterface {
    // Get the user-configured Class Map
    $map = apply_filters( 'timber/post/classmap', [
      'post'       => Post::class,
      'page'       => Post::class,
      // TODO special logic for attachments?
      'attachment' => Attachment::class,
    ] );

		$class = $map[$post->post_type] ?? null;

    // If class is a callable, call it to get the actual class name
    if (is_callable($class)) {
      $class = $class($post);
    }

    // If we don't have a post class by now, fallback on the default class
    $class = $class
      ?? apply_filters( 'timber/post/classmap/default', Post::class );

    // TODO make Core constructors protected
    return new $class($post);
  }
}
