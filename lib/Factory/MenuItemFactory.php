<?php

namespace Timber\Factory;

use WP_Post;

use Timber\CoreInterface;
use Timber\Menu;
use Timber\MenuItem;

/**
 * Internal API class for instantiating Menus
 */
class MenuItemFactory {
	public function from($item, Menu $menu) {
        if (is_numeric($item)) {
			$item = get_post($item);
        }

		if (is_object($item) && $item instanceof WP_Post) {
			return $this->build($item, $menu);
		}

		return false;
	}

	protected function build(WP_Post $item, Menu $menu) : CoreInterface {
		$class = $this->get_menuitem_class($item, $menu);

		return $class::build($item, $menu);
	}

	protected function get_menuitem_class($item, $menu) : string {
		/**
		 * Filters the menu item class
		 *
		 * @since 2.0.0
		 * @example
		 * ```
		 * add_filter( 'timber/menuitem/class', function( $class, $item, $menu ) {
		 *     if ( $item->post_parent ) {
		 *         return SubMenuItem::class;
		 *     }
		 *
		 *     return MenuItem::class;
		 * }, 10, 3 );
		 * ```
		 *
		 * @param string $class The class to use.
		 * @param WP_Post $item The menu item.
		 * @param Menu $menu The menu object.
		 */
		$class = apply_filters( 'timber/menuitem/class', MenuItem::class, $item, $menu );
		return $class;
	}
}
