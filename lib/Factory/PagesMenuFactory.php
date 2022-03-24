<?php

namespace Timber\Factory;

use Timber\CoreInterface;
use Timber\PagesMenu;
use WP_Term;

/**
 * Internal API class for instantiating Menus
 */
class PagesMenuFactory {
	/**
	 * Gets a menu with pages from get_pages().
	 *
     * @param array $args Optional. Args for get_pages().
	 *
	 * @return \Timber\CoreInterface
	 */
	public function from_pages( array $args = [] ) {
		return $this->build( $args );
	}

	/**
	 * Gets the pages menu class.
	 *
	 * @internal
	 *
	 * @return string
	 */
	protected function get_menu_class( $args ) : string {
		/**
		 * Filters the class(es) used for different menus.
		 *
		 * Read more about this in the documentation for [Menu Class Maps](https://timber.github.io/docs/v2/guides/class-maps/#the-menu-class-map).
		 *
		 * The default Menu Class Map will contain class names for locations that map to `Timber\Menu`.
		 *
		 * @since 2.0.0
		 * @example
		 * ```
		 * add_filter( 'timber/pages_menu/class', function( $class ) {
		 *     return ExtendedPagesMenu::class;
		 * } );
		 * ```
		 *
		 * @param array $class The pages menu class to use.
		 */
		$class = apply_filters( 'timber/pages_menu/class', PagesMenu::class );

		// If class is a callable, call it to get the actual class name
		if ( is_callable( $class ) ) {
			$class = $class( $args );
		}

		// Fallback on the default class.
		$class = $class ?? PagesMenu::class;

		return $class;
	}

	/**
	 * Build menu
	 *
	 * @param WP_Term $term
     * @param array $args Optional. Args for get_pages().
	 * @return CoreInterface
	 */
	protected function build( array $args = [] ) : CoreInterface {
		$class = $this->get_menu_class( $args );

		return $class::build( null, $args );
	}
}

