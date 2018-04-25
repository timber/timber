<?php

namespace Timber\Integrations\WooCommerce;

/**
 * Class ProductsIterator
 *
 * @package Timber\Integrations\WooCommerce
 */
class ProductsIterator extends \ArrayIterator {
	/**
	 * Set $product global in addition to $post global.
	 *
	 * For some functionality, WooCommerce works with a global named $product. When looping over multiple product posts,
	 * this global is not automatically set. With this custom ArrayIterator, we can prepare the globals that are needed.
	 *
	 * @see \Timber\PostsIterator::current()
	 *
	 * @return \Timber\Post
	 */
	public function current() {
		global $post, $product;

		$post = parent::current();
		$product = wc_get_product( $post->ID );

		return $post;
	}
}
