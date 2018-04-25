<?php

namespace Timber\Integrations\WooCommerce;

use Timber\Term;

/**
 * Class Product
 *
 * @package Timber\Integrations\WooCommerce
 */
class Product extends \Timber\Post {
	/**
	 * @var null|\WC_Product
	 */
	public $product = null;

	/**
	 * Product constructor.
	 *
	 * @param mixed $post A post object or an object of class WC_Product or a class that inherits from WC_Product.
	 */
	public function __construct( $post = null ) {
		global $product;

		/**
		 * Check if the object is an instance of WC_Product or inherits from WC_Product.
		 *
		 * In that case, get the post ID from the product and then let Timber get the post through the parent
		 * constructor of this class.
		 */
		if ( is_a( $post, 'WC_Product' ) ) {
			parent::__construct( $post->get_id() );
			$product = $post;
		} else {
			parent::__construct( $post );
			$product = wc_get_product( $this->ID );
		}

		$this->product = $product;
	}

	/**
	 * Get a WooCommerce product attribute by slug.
	 *
	 * @param $slug
	 *
	 * @return array|false
	 */
	public function get_product_attribute( $slug ) {
		$attributes = $this->product->get_attributes();

		if ( ! $attributes || empty( $attributes ) ) {
			return false;
		}

		/**
		 * @var \WC_Product_Attribute|false $attribute
		 */
		$attribute = false;

		foreach ( $attributes as $key => $value ) {
			if ( "pa_{$slug}" === $key ) {
				$attribute = $attributes[ $key ];
				break;
			}
		}

		if ( ! $attribute ) {
			return false;
		}

		if ( $attribute->is_taxonomy() ) {
			$terms = wc_get_product_terms(
				$this->product->get_id(),
				$attribute->get_name(),
				array(
					'fields' => 'all',
				)
			);

			// Turn WP_Terms into instances of Timber\Term
			$terms = array_map( function( $term ) {
				return new Term( $term );
			}, $terms );

			return $terms;
		}

		return $attribute->get_options();
	}
}
