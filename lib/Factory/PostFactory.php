<?php

namespace Timber\Factory;

use Timber\Attachment;
use Timber\CoreInterface;
use Timber\Post;

use WP_Query;
use WP_Post;

/**
 * Internal API class for instantiating posts
 */
class PostFactory {
	public function from($params) {
		if (is_int($params) || is_string($params) && is_numeric($params)) {
			return $this->from_id($params);
		}

		if ($params instanceof WP_Query) {
			return $this->from_wp_query($params);
		}

		if (is_object($params)) {
			return $this->from_post_object($params);
		}

		if ($this->is_numeric_array($params)) {
			return array_map([$this, 'from'], $params);
		}

		if (is_array($params)) {
			return $this->from_wp_query(new WP_Query($params));
		}

		return false;
	}

  protected function from_id(int $id) {
		$wp_post = get_post($id);

		if (!$wp_post) {
			return false;
		}

    return $this->build($wp_post);
  }

	protected function from_post_object(object $obj) : CoreInterface {
		if ($obj instanceof CoreInterface) {
			return $obj;
		}

		if ($obj instanceof WP_Post) {
			return $this->build($obj);
		}

		throw new \InvalidArgumentException(sprintf(
			'Expected an instance of Timber\CoreInterface or WP_Post, got %s',
			get_class($obj)
		));
	}

	protected function from_wp_query(WP_Query $query) : Iterable {
		return array_map([$this, 'build'], $query->posts);
	}

	protected function get_post_class( WP_Post $post ) : string {
		$classmap = [
      'post'       => Post::class,
      'page'       => Post::class,
      // @todo special logic for attachments?
      'attachment' => Attachment::class,
		];

		/**
		 * Filters the class(es) used for different post types.
		 *
		 * Read more about this in the documentation for [Post Class Maps](https://timber.github.io/docs/v2/guides/class-maps/#the-post-class-map).
		 *
		 * The default Post Class Map will contain class names for posts, pages that map to
		 * `Timber\Post` and attachments that map to `Timber\Attachment`.
		 *
		 * @since 2.0.0
		 * @example
		 * ```*
		 * use Book;
		 * use Page;
		 *
		 * add_filter( 'timber/post/classmap', function( $classmap ) {
		 *     $custom_classmap = [
		 *         'page' => Page::class,
		 *         'book' => Book::class,
		 *     ];
		 *
		 *     return array_merge( $classmap, $custom_classmap );
		 * } );
		 * ```
		 *
		 * @param string|array $classmap The post class(es) to use. An associative array where the
		 *                               key is the post type and the value the name of the class to
		 *                               use for this post type.
		 */
		$classmap = apply_filters( 'timber/post/classmap', $classmap );

		$class = $classmap[ $post->post_type ] ?? null;

    // If class is a callable, call it to get the actual class name
		if ( is_callable( $class ) ) {
			$class = $class( $post );
    }

    // If we don't have a post class by now, fallback on the default class
    return $class ?? Post::class;
	}

  protected function build(WP_Post $post) : CoreInterface {
		$class = $this->get_post_class($post);

    // @todo make Core constructors protected, call Post::build() here
    return new $class($post);
  }

	protected function is_numeric_array($arr) {
		if ( ! is_array($arr) ) {
			return false;
		}
		foreach (array_keys($arr) as $k) {
			if ( ! is_int($k) ) return false;
		}
		return true;
	}
}
