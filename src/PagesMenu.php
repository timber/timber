<?php

namespace Timber;

use stdClass;
use WP_Post;

/**
 * Class PagesMenu
 *
 * Uses get_pages() to retrieve a list pages and returns it as a Timber menu.
 *
 * @see get_pages()
 *
 * @api
 * @since 2.0.0
 */
class PagesMenu extends Menu
{
    /**
     * Initializes a pages menu.
     *
     * @api
     *
     * @param null  $menu Unused. Only here for compatibility with the Timber\Menu class.
     * @param array $args Optional. Args for wp_list_pages().
     *
     * @return PagesMenu
     */
    public static function build($menu, $args = []): ?self
    {
        /**
         * Default arguments from wp_page_menu() function.
         *
         * @see wp_page_menu()
         *
         * @since 2.3.0 The 'menu' and 'theme_location' are added to provide compatibility with Polylang.
         * @see https://github.com/timber/timber/issues/2922
         */
        $defaults = [
            'sort_column' => 'menu_order, post_title',
            'echo' => true,
            'link_before' => '',
            'link_after' => '',
            'before' => '<ul>',
            'after' => '</ul>',
            'item_spacing' => 'discard',
            'walker' => '',
            'menu' => '',
            'theme_location' => '',
            'menu_id' => '',
            'menu_class' => 'menu',
            'container' => 'div',
        ];

        $args = \wp_parse_args($args, $defaults);

        if (!\in_array($args['item_spacing'], ['preserve', 'discard'], true)) {
            // Invalid value, fall back to default.
            $args['item_spacing'] = $defaults['item_spacing'];
        }

        /**
         * Filters the arguments used to generate a page-based menu.
         *
         * @see wp_page_menu()
         *
         * @param array $args An array of page menu arguments. See wp_page_menu() for information on
         *                    accepted arguments.
         */
        $args = \apply_filters('wp_page_menu_args', $args);

        /**
         * Default arguments from wp_list_pages() function.
         *
         * @see wp_list_pages()
         */
        $defaults = [
            'depth' => 0,
            'show_date' => '',
            'date_format' => \get_option('date_format'),
            'child_of' => 0,
            'exclude' => '',
            'title_li' => \__('Pages'),
            'echo' => 1,
            'authors' => '',
            'sort_column' => 'menu_order, post_title',
            'link_before' => '',
            'link_after' => '',
            'item_spacing' => 'preserve',
            'walker' => '',
        ];

        $args = \wp_parse_args($args, $defaults);

        if (!\in_array($args['item_spacing'], ['preserve', 'discard'], true)) {
            // Invalid value, fall back to default.
            $args['item_spacing'] = $defaults['item_spacing'];
        }

        // Sanitize, mostly to keep spaces out.
        $args['exclude'] = \preg_replace('/[^0-9,]/', '', (string) $args['exclude']);

        // Allow plugins to filter an array of excluded pages (but don't put a nullstring into the array).
        $exclude_array = ($args['exclude'])
            ? \explode(',', $args['exclude'])
            : [];

        /**
         * Filters the array of pages to exclude from the pages list.
         *
         * @param string[] $exclude_array An array of page IDs to exclude.
         */
        $args['exclude'] = \implode(',', \apply_filters('wp_list_pages_excludes', $exclude_array));

        $args['hierarchical'] = 0;

        $pages_menu = new static(null, $args);

        // Query pages.
        $menu_items = \get_pages($pages_menu->args);

        if (!empty($menu_items)) {
            $menu_items = \array_map([$pages_menu, 'pre_setup_nav_menu_item'], $menu_items);
            $menu_items = \array_map('wp_setup_nav_menu_item', $menu_items);

            /**
             * Can’t really apply the "wp_get_nav_menu_items" filter here, because we don’t have a
             * $menu object to pass in.
             */

            \_wp_menu_item_classes_by_context($menu_items);

            $menu_items_with_children = [];

            foreach ((array) $menu_items as $menu_item) {
                if ($menu_item->menu_item_parent) {
                    $menu_items_with_children[$menu_item->menu_item_parent] = true;
                }
            }

            // Add the menu-item-has-children class where applicable.
            if (!empty($menu_items_with_children)) {
                foreach ($menu_items as &$menu_item) {
                    if (isset($menu_items_with_children[$menu_item->ID])) {
                        $menu_item->classes[] = 'menu-item-has-children';
                    }
                }
            }

            unset($menu_item);

            if (\is_array($menu_items)) {
                /**
                 * Filters the arguments used to display a navigation menu.
                 *
                 * @see wp_nav_menu()
                 *
                 * @param array $args Array of wp_nav_menu() arguments.
                 */
                $args = \apply_filters('wp_nav_menu_args', $args);
                $args = (object) $args;

                /**
                 * Filters the sorted list of menu item objects before generating the menu's HTML.
                 *
                 * @param array     $menu_items The menu items, sorted by each menu item's menu order.
                 * @param stdClass $args       An object containing wp_nav_menu() arguments.
                 */
                $menu_items = \apply_filters('wp_nav_menu_objects', $menu_items, $args);

                $menu_items = $pages_menu->convert_menu_items($menu_items);
                $menu_items = $pages_menu->order_children($menu_items);
                $menu_items = $pages_menu->strip_to_depth_limit($menu_items);
            }

            $pages_menu->items = $menu_items;
        }

        /**
         * Since Timber doesn’t use HTML, serialize the menu object to provide a cacheable string.
         *
         * Certain caching plugins will use this filter to cache a menu and return it early in the
         * `pre_wp_nav_menu` filter.
         *
         * We can’t use the result of this filter, because it would return a string. That’s why we
         * don’t assign the result of the filter to a variable.
         *
         * @see wp_nav_menu()
         */
        \apply_filters('wp_nav_menu', \serialize($pages_menu), $args);

        return $pages_menu;
    }

    /**
     * Sets up properties needed for mocking nav menu items.
     *
     * We need to set some properties so that we can use `wp_setup_nav_menu_item()` on the menu
     * items and a proper menu item hierarchy can be built.
     *
     * @param WP_Post $post A post object.
     *
     * @return WP_Post Updated post object.
     */
    protected function pre_setup_nav_menu_item($post)
    {
        $post->object_id = $post->ID;
        $post->menu_item_parent = $post->post_parent;
        $post->object = $post->post_type;
        $post->post_type = 'nav_menu_item';
        $post->type = 'post_type';

        return $post;
    }
}
