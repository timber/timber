<?php

namespace Timber;

use Throwable;
use Timber\Factory\MenuItemFactory;
use WP_Term;

/**
 * Class Menu
 *
 * @api
 */
class Menu extends CoreEntity
{
    /**
     * The underlying WordPress Core object.
     *
     * @since 2.0.0
     *
     * @var WP_Term|null
     */
    protected ?WP_Term $wp_object;

    public $object_type = 'term';

    /**
     * @api
     * @var integer The depth of the menu we are rendering
     */
    public $depth;

    /**
     * @api
     * @var array|null Array of `Timber\Menu` objects you can to iterate through.
     */
    public $items = null;

    /**
     * @api
     * @var int The ID of the menu, corresponding to the wp_terms table.
     */
    public $id;

    /**
     * @api
     * @var int The ID of the menu, corresponding to the wp_terms table.
     */
    public $ID;

    /**
     * @api
     * @var int The ID of the menu, corresponding to the wp_terms table.
     */
    public $term_id;

    /**
     * @api
     * @var string The name of the menu (ex: `Main Navigation`).
     */
    public $name;

    /**
     * Menu slug.
     *
     * @api
     * @var string The menu slug.
     */
    public $slug;

    /**
     * @api
     * @var string The name of the menu (ex: `Main Navigation`).
     */
    public $title;

    /**
     * Menu args.
     *
     * @api
     * @since 1.9.6
     * @var object An object of menu args.
     */
    public $args;

    /**
     * @var MenuItem the current menu item
     */
    private $_current_item;

    /**
     * @api
     * @var array The unfiltered args sent forward via the user in the __construct
     */
    public $raw_args;

    /**
     * Theme Location.
     *
     * @api
     * @since 1.9.6
     * @var string The theme location of the menu, if available.
     */
    public $theme_location = null;

    /**
     * Sorted menu items.
     *
     * @var array
     */
    protected $sorted_menu_items = [];

    /**
     * @internal
     * @param WP_Term   $menu The vanilla WordPress term object to build from.
     * @param array      $args Optional. Right now, only the `depth` is
     *                            supported which says how many levels of hierarchy should be
     *                            included in the menu. Default `0`, which is all levels.
     * @return Menu
     */
    public static function build(?WP_Term $menu, $args = []): ?self
    {
        /**
         * Default arguments from wp_nav_menu() function.
         *
         * @see wp_nav_menu()
         */
        $defaults = [
            'menu' => '',
            'container' => 'div',
            'container_class' => '',
            'container_id' => '',
            'container_aria_label' => '',
            'menu_class' => 'menu',
            'menu_id' => '',
            'echo' => true,
            'fallback_cb' => 'wp_page_menu',
            'before' => '',
            'after' => '',
            'link_before' => '',
            'link_after' => '',
            'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
            'item_spacing' => 'preserve',
            'depth' => 0,
            'walker' => '',
            'theme_location' => '',
        ];

        $args = \wp_parse_args($args, $defaults);

        if (!\in_array($args['item_spacing'], ['preserve', 'discard'], true)) {
            // Invalid value, fall back to default.
            $args['item_spacing'] = $defaults['item_spacing'];
        }

        /**
         * @see wp_nav_menu()
         */
        $args = \apply_filters('wp_nav_menu_args', $args);
        $args = (object) $args;

        /**
         * Since Timber doesn't use HTML here, try to unserialize the maybe cached menu object
         *
         * @see wp_nav_menu()
         */
        $nav_menu = \apply_filters('pre_wp_nav_menu', null, $args);
        if (null !== $nav_menu) {
            try {
                $nav_menu = \unserialize($nav_menu);
                if ($nav_menu instanceof Menu) {
                    return $nav_menu;
                }
            } catch (Throwable $e) {
            }
        }

        /**
         * No valid menu term provided.
         *
         * In earlier versions, Timber returned a pages menu if no menu was found. Now, it returns
         * null. If you still need the pages menu, you can use Timber\Timber::get_pages_menu().
         *
         * @see \Timber\Timber::get_pages_menu()
         */
        if (!$menu) {
            return null;
        }

        // Skip the menu term guessing part, we already have our menu term

        $menu_items = \wp_get_nav_menu_items($menu->term_id, [
            'update_post_term_cache' => false,
        ]);

        \_wp_menu_item_classes_by_context($menu_items);

        $sorted_menu_items = [];
        $menu_items_with_children = [];
        foreach ((array) $menu_items as $menu_item) {
            $sorted_menu_items[$menu_item->menu_order] = $menu_item;
            if ($menu_item->menu_item_parent) {
                $menu_items_with_children[$menu_item->menu_item_parent] = true;
            }
        }

        // Add the menu-item-has-children class where applicable.
        if ($menu_items_with_children) {
            foreach ($sorted_menu_items as &$menu_item) {
                if (isset($menu_items_with_children[$menu_item->ID])) {
                    $menu_item->classes[] = 'menu-item-has-children';
                }
            }
        }

        unset($menu_items, $menu_item);

        /**
         * @see wp_nav_menu()
         */
        $sorted_menu_items = \apply_filters('wp_nav_menu_objects', $sorted_menu_items, $args);

        /**
         * Filters the sorted list of menu item objects before creating the Menu object.
         *
         * @since 2.0.0
         * @example
         * ```
         * add_filter( 'timber/menu/item_objects', function ( $items ) {
         *     return array_map(function ($item) {
         *         if ( is_object( $item ) && ! ( $item instanceof \WP_Post ) ) {
         *             return new \WP_Post( get_object_vars( $item ) );
         *         }
         *
         *         return $item;
         *     }, $items);
         * } );
         * ```
         *
         * @param array<mixed> $item
         * @param WP_Term $menu
         */
        $sorted_menu_items = \apply_filters('timber/menu/item_objects', $sorted_menu_items, $menu);

        // Create Menu object
        $nav_menu = new static($menu, (array) $args);
        $nav_menu->sorted_menu_items = $sorted_menu_items;

        // Convert items into MenuItem objects
        $sorted_menu_items = $nav_menu->convert_menu_items($sorted_menu_items);
        $sorted_menu_items = $nav_menu->order_children($sorted_menu_items);
        $sorted_menu_items = $nav_menu->strip_to_depth_limit($sorted_menu_items);

        $nav_menu->items = $sorted_menu_items;
        unset($sorted_menu_items);

        /**
         * Since Timber doesn't use HTML, serialize the menu object to provide a cacheable string
         *
         * @see wp_nav_menu()
         */
        $_nav_menu = \apply_filters('wp_nav_menu', \serialize($nav_menu), $args);

        return $nav_menu;
    }

    /**
     * Initialize a menu.
     *
     * @api
     *
     * @param WP_Term|null $term A menu slug, the term ID of the menu, the full name from the admin
     *                            menu, the slug of the registered location or nothing. Passing
     *                            nothing is good if you only have one menu. Timber will grab what
     *                            it finds.
     * @param array $args         Optional. Right now, only the `depth` is supported which says how
     *                            many levels of hierarchy should be included in the menu. Default
     *                            `0`, which is all levels.
     */
    protected function __construct(?WP_Term $term, array $args = [])
    {
        // For future enhancements?
        $this->raw_args = $args;
        $this->args = (object) $args;

        if (isset($this->args->depth)) {
            $this->depth = (int) $this->args->depth;
        }

        if (!$term) {
            return;
        }

        // Set theme location if available
        $locations = \array_flip(\array_filter(\get_nav_menu_locations(), fn ($location) => \is_string($location) || \is_int($location)));

        $this->theme_location = $locations[$term->term_id] ?? null;

        if ($this->theme_location) {
            $this->args->theme_location = $this->theme_location;
        }

        $this->import($term);
        $this->ID = $this->term_id;
        $this->id = $this->term_id;
        $this->wp_object = $term;
        $this->title = $this->name;
    }

    /**
     * Gets the underlying WordPress Core object.
     *
     * @since 2.0.0
     *
     * @return WP_Term|null
     */
    public function wp_object(): ?WP_Term
    {
        return $this->wp_object;
    }

    /**
     * Convert menu items into MenuItem objects
     *
     * @param array $menu_items
     * @return MenuItem[]
     */
    protected function convert_menu_items(array $menu_items): array
    {
        $menu_item_factory = new MenuItemFactory();
        return \array_map(function ($item) use ($menu_item_factory): MenuItem {
            return $menu_item_factory->from($item, $this);
        }, $menu_items);
    }

    /**
     * Find a parent menu item in a set of menu items.
     *
     * @api
     * @param array $menu_items An array of menu items.
     * @param int   $parent_id  The parent ID to look for.
     * @return MenuItem|null A menu item. False if no parent was found.
     */
    public function find_parent_item_in_menu(array $menu_items, int $parent_id): ?MenuItem
    {
        foreach ($menu_items as $item) {
            if ($item->ID === $parent_id) {
                return $item;
            }
        }
        return null;
    }

    /**
     * @internal
     * @param array $items
     * @return MenuItem[]
     */
    protected function order_children(array $items): array
    {
        $items_by_id = [];
        $menu_items = [];

        foreach ($items as $item) {
            // Index each item by its ID
            $items_by_id[$item->ID] = $item;
        }

        // Walk through our indexed items and assign them to their parents as applicable
        foreach ($items_by_id as $item) {
            if (!empty($item->menu_item_parent) && isset($items_by_id[$item->menu_item_parent])) {
                // This one is a child item, add it to its parent
                $items_by_id[$item->menu_item_parent]->add_child($item);
            } else {
                // This is a top-level item, add it as such
                $menu_items[] = $item;
            }
        }
        return $menu_items;
    }

    /**
     * @internal
     * @param array $menu_items
     */
    protected function strip_to_depth_limit(array $menu_items, int $current = 1): array
    {
        $depth = (int) $this->depth; // Confirms still int.
        if ($depth <= 0) {
            return $menu_items;
        }

        foreach ($menu_items as &$current_item) {
            if ($current === $depth) {
                $current_item->remove_class('menu-item-has-children');
                $current_item->children = false;
                continue;
            }

            $current_item->children = $this->strip_to_depth_limit($current_item->children, $current + 1);
        }

        return $menu_items;
    }

    /**
     * Gets a menu meta value.
     *
     * @api
     * @deprecated 2.0.0, use `{{ menu.meta('field_name') }}` instead.
     * @see \Timber\Menu::meta()
     *
     * @param string $field_name The field name for which you want to get the value.
     * @return mixed The meta field value.
     */
    public function get_field($field_name = null)
    {
        Helper::deprecated(
            "{{ menu.get_field('field_name') }}",
            "{{ menu.meta('field_name') }}",
            '2.0.0'
        );

        return $this->meta($field_name);
    }

    /**
     * Get menu items.
     *
     * Instead of using this function, you can use the `$items` property directly to get the items
     * for a menu.
     *
     * @api
     * @example
     * ```twig
     * {% for item in menu.get_items %}
     *     <a href="{{ item.link }}">{{ item.title }}</a>
     * {% endfor %}
     * ```
     * @return array Array of `Timber\MenuItem` objects. Empty array if no items could be found.
     */
    public function get_items()
    {
        if (\is_array($this->items)) {
            return $this->items;
        }

        return [];
    }

    /**
     * Get the current MenuItem based on the WP context
     *
     * @see _wp_menu_item_classes_by_context()
     * @example
     * Say you want to render the sub-tree of the main menu that corresponds
     * to the menu item for the current page, such as in a context-aware sidebar:
     * ```twig
     * <div class="sidebar">
     *   <a href="{{ menu.current_item.link }}">
     *     {{ menu.current_item.title }}
     *   </a>
     *   <ul>
     *     {% for child in menu.current_item.children %}
     *       <li>
     *         <a href="{{ child.link }}">{{ child.title }}</a>
     *       </li>
     *     {% endfor %}
     *   </ul>
     * </div>
     * ```
     * @param int $depth the maximum depth to traverse the menu tree to find the
     * current item. Defaults to null, meaning no maximum. 1-based, meaning the
     * top level is 1.
     * @return MenuItem the current `Timber\MenuItem` object, i.e. the menu item
     * corresponding to the current post.
     */
    public function current_item($depth = null)
    {
        if (false === $this->_current_item) {
            // I TOLD YOU BEFORE.
            return false;
        }

        if (empty($this->items)) {
            $this->_current_item = false;
            return $this->_current_item;
        }

        if (!isset($this->_current_item)) {
            $current = $this->traverse_items_for_current(
                $this->items,
                $depth
            );

            if (\is_null($depth)) {
                $this->_current_item = $current;
            } else {
                return $current;
            }
        }

        return $this->_current_item;
    }

    /**
     * Alias for current_top_level_item(1).
     *
     * @return MenuItem the current top-level `Timber\MenuItem` object.
     */
    public function current_top_level_item()
    {
        return $this->current_item(1);
    }

    /**
     * Traverse an array of MenuItems in search of the current item.
     *
     * @internal
     * @param array $items the items to traverse.
     */
    private function traverse_items_for_current($items, $depth)
    {
        $current = false;
        $currentDepth = 1;
        $i = 0;

        while (isset($items[$i])) {
            $item = $items[$i];

            if ($item->current) {
                // cache this item for subsequent calls.
                $current = $item;
                // stop looking.
                break;
            } elseif ($item->current_item_ancestor) {
                // we found an ancestor,
                // but keep looking for a more precise match.
                $current = $item;

                if ($currentDepth === $depth) {
                    // we're at max traversal depth.
                    return $current;
                }

                // we're in the right subtree, so go deeper.
                if ($item->children()) {
                    // reset the counter, since we're at a new level.
                    $items = $item->children();
                    $i = 0;
                    $currentDepth++;
                    continue;
                }
            }

            $i++;
        }

        return $current;
    }

    public function __toString()
    {
        static $menu_id_slugs = [];

        $args = $this->args;

        $items = '';
        $nav_menu = '';
        $show_container = false;

        if ($args->container) {
            /**
            * Filters the list of HTML tags that are valid for use as menu containers.
            *
            * @since 3.0.0
            *
            * @param string[] $tags The acceptable HTML tags for use as menu containers.
            *                       Default is array containing 'div' and 'nav'.
            */
            $allowed_tags = \apply_filters('wp_nav_menu_container_allowedtags', ['div', 'nav']);

            if (\is_string($args->container) && \in_array($args->container, $allowed_tags, true)) {
                $show_container = true;
                $class = $args->container_class ? ' class="' . \esc_attr($args->container_class) . '"' : ' class="menu-' . $this->slug . '-container"';
                $id = $args->container_id ? ' id="' . \esc_attr($args->container_id) . '"' : '';
                $aria_label = ('nav' === $args->container && $args->container_aria_label) ? ' aria-label="' . \esc_attr($args->container_aria_label) . '"' : '';
                $nav_menu .= '<' . $args->container . $id . $class . $aria_label . '>';
            }
        }

        $items .= \walk_nav_menu_tree($this->sorted_menu_items, $args->depth, $args);

        // Attributes.
        if (!empty($args->menu_id)) {
            $wrap_id = $args->menu_id;
        } else {
            $wrap_id = 'menu-' . $this->slug;

            while (\in_array($wrap_id, $menu_id_slugs, true)) {
                if (\preg_match('#-(\d+)$#', $wrap_id, $matches)) {
                    $wrap_id = \preg_replace('#-(\d+)$#', '-' . ++$matches[1], $wrap_id);
                } else {
                    $wrap_id = $wrap_id . '-1';
                }
            }
        }
        $menu_id_slugs[] = $wrap_id;

        $wrap_class = $args->menu_class ? $args->menu_class : '';

        $nav_menu .= \sprintf($args->items_wrap, \esc_attr($wrap_id), \esc_attr($wrap_class), $items);
        if ($show_container) {
            $nav_menu .= '</' . $args->container . '>';
        }

        return $nav_menu;
    }

    /**
     * Checks whether the current user can edit the menu.
     *
     * @api
     * @since 2.0.0
     * @return bool
     */
    public function can_edit(): bool
    {
        return \current_user_can('edit_theme_options');
    }
}
