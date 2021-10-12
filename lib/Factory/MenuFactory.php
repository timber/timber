<?php

namespace Timber\Factory;

use Timber\CoreInterface;
use Timber\Menu;

use WP_Term;

/**
 * Internal API class for instantiating Menus
 */
class MenuFactory {
	public function from($params, array $args = []) {
		if ($params === 0) {
			// We know no nav_menu term exists with ID 0,
			// so just look at the existing terms in the database.
			return $this->from_nav_menu_terms($args);
		}

		if (is_numeric($params)) {
			return $this->from_id((int) $params, $args);
		}

		if (is_string($params)) {
			return $this->from_string($params, $args);
		}

		if (is_object($params)) {
			return $this->from_term_object($params, $args);
		}

		// Fall back on the first nav_menu term we find.
		return $this->from_nav_menu_terms($args);
	}

	/**
	 * Query the database for existing nav menu terms and return the first one
	 * as a Timber\Menu object.
	 *
	 * @internal
	 */
	protected function from_nav_menu_terms(array $args) {
		$terms = get_terms('nav_menu', [
			'hide_empty' => true,
		]);

		$id = $terms[0]->term_id ?? 0;

		return $id ? $this->from_id($id, $args) : false;
	}

	/**
	 * Get a Menu by its ID
	 *
	 * @internal
	 */
	protected function from_id($id, $args) {
		// WP Menus are WP_Term objects under the hood.
		$term = wp_get_nav_menu_object($id);

		if (!$term) {
			return false;
		}

		return $this->build($term, $args);
	}

	/**
	 * Get a Menu by its slug or name, or by a nav menu location name, e.g. "primary-menu"
	 *
	 * @internal
	 */
	protected function from_string($ident, $args) {
		$term = get_term_by('slug', $ident, 'nav_menu') ?: get_term_by('name', $ident, 'nav_menu');

		if (!$term) {
			$locations = get_nav_menu_locations();
			if (isset($locations[$ident])) {
				$id   = apply_filters('timber/menu/id_from_location', $locations[$ident]);
				$term = wp_get_nav_menu_object($id);
			}
		}

		if (!$term) {
			return false;
		}

		return $this->build($term, $args);
	}

	/**
	 * Get a menu from object
	 *
	 * @internal
	 */
	protected function from_term_object(object $obj, array $args) : CoreInterface {
		if ($obj instanceof CoreInterface) {
			// We already have a Timber Core object of some kind
			return $obj;
		}

		if ($obj instanceof WP_Term) {
			return $this->build($obj, $args);
		}

		throw new \InvalidArgumentException(sprintf(
			'Expected an instance of Timber\CoreInterface or WP_Term, got %s',
			get_class($obj)
		));
	}

	/**
	 * Get a menu class
	 *
	 * @internal
	 */
	protected function get_menu_class($term, $args) : string {
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
		$classmap = apply_filters( 'timber/menu/classmap', [] );

		$location = $this->get_menu_location($term);

		$class = $classmap[$location] ?? null;

		// If class is a callable, call it to get the actual class name
		if (is_callable($class)) {
			$class = $class($term, $args);
		}

		$class = $class ?? Menu::class;

		/**
		 * Filters the menu class based on your custom criterias.
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
		$class = apply_filters( 'timber/menu/class', $class, $term, $args );

		// Fallback on the default class
		return $class;
	}

	protected function get_menu_location(WP_Term $term) : ?string {
        $locations = array_flip(get_nav_menu_locations());
		return $locations[$term->term_id] ?? null;
    }

	protected function build(WP_Term $term, array $args) : CoreInterface {
		$class = $this->get_menu_class($term, $args);

		return $class::build($term, $args);
	}
}

