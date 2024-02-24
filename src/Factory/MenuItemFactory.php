<?php

namespace Timber\Factory;

use Timber\CoreInterface;
use Timber\Menu;
use Timber\MenuItem;
use WP_Post;

/**
 * Internal API class for instantiating Menus
 */
class MenuItemFactory
{
    /**
     * Create a new MenuItem from a WP_Post or post id
     *
     * @param int|WP_Post $item
     * @param Menu $menu
     * @return MenuItem|null
     */
    public function from($item, Menu $menu): ?MenuItem
    {
        if (\is_numeric($item)) {
            $item = \get_post($item);
        }

        if (\is_object($item) && $item instanceof WP_Post) {
            return $this->build($item, $menu);
        }

        return null;
    }

    protected function build(WP_Post $item, Menu $menu): CoreInterface
    {
        $class = $this->get_menuitem_class($item, $menu);

        return $class::build($item, $menu);
    }

    protected function get_menuitem_class(WP_Post $item, Menu $menu): string
    {
        /**
         * Filters the class(es) used for different menu items.
         *
         * Read more about this in the documentation for [Menu Item Class Maps](https://timber.github.io/docs/v2/guides/class-maps/#the-menu-item-class-map).
         *
         * The default Menu Item Class Map will contain class names for locations that map to `Timber\MenuItem`.
         *
         * @since 2.0.0
         * @example
         * ```
         * add_filter( 'timber/menuitem/classmap', function( $classmap ) {
         *     $custom_classmap = [
         *         'primary'   => MenuItemFooter::class,
         *         'secondary' => MenuItemHeader::class,
         *     ];
         *
         *     return array_merge( $classmap, $custom_classmap );
         * } );
         * ```
         *
         * @param array $classmap The menu item class(es) to use. An associative array where the key is
         *                        the location and the value the name of the class to use for this
         *                        menu item or a callback that determines the class to use.
         */
        $classmap = \apply_filters('timber/menuitem/classmap', []);

        $class = $classmap[$menu->theme_location] ?? null;

        // If class is a callable, call it to get the actual class name
        if (\is_callable($class)) {
            $class = $class($item, $menu);
        }

        // Fallback on the default class
        $class = $class ?? MenuItem::class;

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
        $class = \apply_filters('timber/menuitem/class', $class, $item, $menu);
        return $class;
    }
}
