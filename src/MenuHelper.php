<?php

namespace Timber;

use WP_Term;

/**
 * Class MenuHelper
 *
 * @api
 */
class MenuHelper
{
    /**
     * Get the navigation menu location assigned to the given menu.
     *
     * @return string|null
     */
    public static function get_menu_location(WP_Term $term): ?string
    {
        $locations = \array_flip(static::get_menu_locations());
        return $locations[$term->term_id] ?? null;
    }

    /**
     * Get the navigation menu locations with assigned menus.
     *
     * @return array<string, (int|string)>
     */
    public static function get_menu_locations(): array
    {
        $locations = \array_filter(
            \get_nav_menu_locations(),
            fn ($location) => \is_string($location) || \is_int($location)
        );

        /**
         * Filters the registered navigation menu locations with assigned menus.
         *
         * This filter is used by the WPML integration.
         *
         * @see get_nav_menu_locations()
         * @since 2.0.0
         *
         * @param array<string, (int|string)> $locations
         */
        return \apply_filters('timber/menu_helper/menu_locations', $locations);
    }
}
