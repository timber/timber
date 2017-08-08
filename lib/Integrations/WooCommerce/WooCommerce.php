<?php

namespace Timber\Integrations\WooCommerce;

use Timber\Timber;
use Twig_SimpleFilter;

/**
 * Class WooCommerce
 *
 * Tries to make it as easy as possible to work with WooCommerce when using Timber.
 *
 * When accessing WooCommerce posts, they need to be returned in a Timber\PostCollection that can be looped over with a
 * custom iterator that automatically sets the $product global for each product.
 *
 * @package Timber\Integrations\WooCommerce
 */
class WooCommerce {
	public static $product_class = 'Timber\Integrations\WooCommerce\Product';
	public static $product_iterator = '\Timber\Integrations\WooCommerce\ProductsIterator';

	/**
	 * WooCommerce constructor.
	 */
	public function __construct() {
		// For conditional functions to work, we need too hook into the 'wp' action.
		add_action( 'wp', array( $this, 'setup' ), 20 );

		add_filter( 'timber/twig', array( $this, 'customize_twig' ) );
	}

	/**
	 * Setup filters to use when in WooCommerce context.
	 */
	public function setup() {
		if ( is_woocommerce() ) {
			// Set a custom iterator to correctly set the $product global.
			add_filter( 'timber/class/posts_iterator', array( $this, 'set_product_iterator' ) );

			// Use a custom post class to load all posts when in WooCommerce context.
			add_filter( 'Timber\PostClassMap', array( $this, 'set_product_class' ) );
		}
	}

	/**
	 * Set the iterator to use to loop over post collections.
	 *
	 * @return string
	 */
	public function set_product_iterator() {
		return self::$product_iterator;
	}

	/**
	 * Set the post class to use for product posts.
	 *
	 * @return string
	 */
	public function set_product_class() {
		return self::$product_class;
	}

	/**
	 * Enrich Twig with WooCommerce functionality.
	 *
	 * @param \Twig_Environment $twig
	 * @return \Twig_Environment
	 */
	public function customize_twig( $twig ) {
		$twig->addFilter( new Twig_SimpleFilter( 'to_products', array( $this, 'to_products' ) ) );

		return $twig;
	}

	/**
	 * Turn posts into an array of Timber Product posts.
	 *
	 * @param array|int|string $posts An array of posts or an array of post IDs.
	 *
	 * @return array|\Timber\PostCollection
	 */
	public function to_products( $posts, $apply_visibility_filter = true ) {
		$ids = [];

		if ( ! is_array( $posts ) && is_numeric( $posts ) ) {
			$posts = array( (int) $posts );
		}

		// Convert posts into an array of post IDs.
		foreach ( $posts as $post ) {
			$id = null;

			if ( is_numeric( $post ) ) {
				$id = $post;
			} elseif ( is_a( $post, 'WC_Product' ) ) {
				$id = $post->get_id();
				$apply_visibility_filter = false;
			} elseif ( isset( $post->ID ) ) {
				$id = $post->ID;
			}

			if ( $id ) {
				$ids[] = $id;
			}
		}

		// Get an instance of Timber\PostCollection
		$posts = Timber::get_posts( $ids, self::$product_class, true );

		if ( ! $apply_visibility_filter ) {
			return $posts;
		}

		// Filter to only show visible posts.
		$posts->filter( function( $post ) {
			return $post->product->is_visible();
		} );

		return $posts;
	}
}
