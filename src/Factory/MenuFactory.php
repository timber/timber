<?php

namespace Timber\Factory;

use InvalidArgumentException;
use Timber\CoreInterface;
use Timber\Menu;
use Timber\Timber;
use WP_Term;

/**
 * Internal API class for instantiating Menus
 */
class MenuFactory
{
    /**
     * Tries to get a menu by all means available in an order that matches the most common use
     * cases.
     *
     * Will fall back on the first menu found if no parameters are provided. If no menu is found
     * with the given parameters, it will return null.
     *
     * Note that this method has pitfalls and might not be the most performant way to get a menu.
     *
     *
     * @return Menu|null
     */
    public function from(mixed $params, array $args = []): ?Menu
    {
        $menu = null;

        if (empty($params)) {
            return $this->from_nav_menu_terms($args);
        }

        // If $params is a numeric slug, we might get the wrong menu
        if (\is_numeric($params)) {
            $menu = $this->from_id((int) $params, $args);
        }

        if (\is_object($params)) {
            $menu = $this->from_object($params, $args);
        }

        if (!$menu && \is_string($params)) {
            // If $location is the same than some menu slug, we might get the wrong menu
            $menu = $this->from_location($params, $args);
            if (!$menu) {
                $menu = $this->from_slug($params, $args);
            }
            if (!$menu) {
                $menu = $this->from_name($params, $args);
            }
        }

        return $menu;
    }

    /**
     * Get a Menu from its location
     *
     * @return Menu|null
     */
    protected function from_nav_menu_terms(array $args = []): ?Menu
    {
        $menus = \wp_get_nav_menus();
        foreach ($menus as $menu_maybe) {
            $menu_items = \wp_get_nav_menu_items($menu_maybe->term_id, [
                'update_post_term_cache' => false,
            ]);
            if ($menu_items) {
                $menu = $menu_maybe;
                break;
            }
        }
        return isset($menu) ? $this->from_object($menu, $args) : null;
    }

    /**
     * Get a Menu from its location
     *
     * @return Menu|null
     */
    public function from_location(string $location, array $args = []): ?Menu
    {
        $locations = Timber::get_menu_locations();
        if (!isset($locations[$location])) {
            return null;
        }

        $term = \get_term_by('id', $locations[$location], 'nav_menu');
        if (!$term) {
            return null;
        }

        $args['location'] = $location;

        return $this->build($term, $args);
    }

    /**
     * Get a Menu by its ID
     *
     * @internal
     */
    public function from_id(int $id, array $args = []): ?Menu
    {
        if (0 === $id) {
            return null;
        }

        $term = \get_term_by('id', $id, 'nav_menu');

        if (!$term) {
            return null;
        }

        $args['menu'] = $id;

        return $this->build($term, $args);
    }

    /**
     * Get a Menu by its slug
     *
     * @internal
     */
    public function from_slug(string $slug, array $args = []): ?Menu
    {
        $term = \get_term_by('slug', $slug, 'nav_menu');

        if (!$term) {
            return null;
        }

        $args['menu'] = $slug;

        return $this->build($term, $args);
    }

    /**
     * Get a Menu by its name
     *
     * @internal
     */
    public function from_name(string $name, array $args = []): ?Menu
    {
        $term = \get_term_by('name', $name, 'nav_menu');

        if (!$term) {
            return null;
        }

        $args['menu'] = $name;

        return $this->build($term, $args);
    }

    /**
     * Get a menu from object
     *
     * @internal
     */
    protected function from_object(object $obj, array $args = []): ?Menu
    {
        if ($obj instanceof Menu) {
            // We already have a Timber Core object of some kind
            return $obj;
        }

        if ($obj instanceof WP_Term) {
            $args['menu'] = $obj;
            return $this->build($obj, $args);
        }

        throw new InvalidArgumentException(\sprintf(
            'Expected an instance of Timber\CoreInterface or WP_Term, got %s',
            $obj::class
        ));
    }

    /**
     * Get a menu class
     *
     * @internal
     */
    protected function get_menu_class($term, $args): string
    {
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
         * add_filter( 'timber/menu/classmap', function( $classmap ) {
         *     $custom_classmap = [
         *         'primary'   => MenuPrimary::class,
         *         'secondary' => MenuSecondary::class,
         *     ];
         *
         *     return array_merge( $classmap, $custom_classmap );
         * } );
         * ```
         *
         * @param array $classmap The menu class(es) to use. An associative array where the key is
         *                        the location and the value the name of the class to use for this
         *                        menu or a callback that determines the class to use.
         */
        $classmap = \apply_filters('timber/menu/classmap', []);

        $location = Timber::get_menu_location($term);

        $class = $classmap[$location] ?? null;

        // If class is a callable, call it to get the actual class name
        if (\is_callable($class)) {
            $class = $class($term, $args);
        }

        // Fallback on the default class
        $class ??= Menu::class;

        /**
         * Filters the menu class based on your custom criteria.
         *
         * Maybe the location is not appropriate in some cases. This filter will allow you to filter the class
         * on whatever data is available.
         *
         * @since 2.0.0
         * @example
         * ```
         * add_filter( 'timber/menu/class', function( $class, $term, $args ) {
         *     if ( $args['depth'] === 1 ) {
         *         return SingleLevelMenu::class;
         *     }
         *
         *     return MultiLevelMenu::class;
         * }, 10, 3 );
         * ```
         *
         * @param string $class The class to use.
         * @param WP_Term $term The menu term.
         * @param array $args The arguments passed to the menu.
         */
        $class = \apply_filters('timber/menu/class', $class, $term, $args);

        return $class;
    }

    /**
     * Build menu
     *
     * @param array $args
     * @return CoreInterface
     */
    protected function build(WP_Term $term, $args): CoreInterface
    {
        $class = $this->get_menu_class($term, $args);

        return $class::build($term, $args);
    }
}
