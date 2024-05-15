<?php

namespace Timber\Factory;

use Timber\CoreInterface;
use Timber\PagesMenu;

/**
 * Internal API class for instantiating Menus
 */
class PagesMenuFactory
{
    /**
     * Gets a menu with pages from get_pages().
     *
     * @param array $args Optional. Args for get_pages().
     *
     * @return CoreInterface
     */
    public function from_pages(array $args = [])
    {
        return $this->build($args);
    }

    /**
     * Gets the pages menu class.
     *
     * @internal
     *
     * @return string
     */
    protected function get_menu_class($args): string
    {
        /**
         * Filters the class used for different menus.
         *
         * Read more about this in the documentation for [Pages Menu Class filter](https://timber.github.io/docs/v2/guides/class-maps/#the-pages-menu-class-filter).
         *
         * @since 2.0.0
         * @example
         * ```
         * add_filter( 'timber/pages_menu/class', function( $class ) {
         *     return ExtendedPagesMenu::class;
         * } );
         * ```
         *
         * @param string $class The pages menu class to use.
         * @param array  $args  The arguments passed to `Timber::get_pages_menu()`.
         */
        $class = \apply_filters('timber/pages_menu/class', PagesMenu::class, $args);

        // If class is a callable, call it to get the actual class name
        if (\is_callable($class)) {
            $class = $class($args);
        }

        // Fallback on the default class.
        $class ??= PagesMenu::class;

        return $class;
    }

    /**
     * Build menu
     *
     * @param array $args Optional. Args for get_pages().
     * @return CoreInterface
     */
    protected function build(array $args = []): CoreInterface
    {
        $class = $this->get_menu_class($args);

        return $class::build(null, $args);
    }
}
