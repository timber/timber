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
	 * @param null|\Timber\Post|\WP_Post $post A post object.
	 */
	public function __construct( $post = null ) {
		parent::__construct( $post );

		global $product;

		$product = wc_get_product( $this->ID );
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
