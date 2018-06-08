<?php

namespace Timber;

use Timber\Core;
use Timber\Post;

class Menu extends Core {

	public $MenuItemClass = 'Timber\MenuItem';
	public $PostClass = 'Timber\Post';

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
	 * @var integer The ID of the menu, corresponding to the wp_terms table.
	 */
	public $id;

	/**
	 * @api
	 * @var integer The ID of the menu, corresponding to the wp_terms table.
	 */
	public $ID;

	/**
	 * @api
	 * @var integer The ID of the menu, corresponding to the wp_terms table.
	 */
	public $term_id;

	/**
	 * @api
	 * @var string The name of the menu (ex: `Main Navigation`).
	 */
	public $name;

	/**
	 * @api
	 * @var array The unfiltered options sent forward via the user in the __construct
	 */
	public $raw_options;

	/**
	 * @api
	 * @var string The name of the menu (ex: `Main Navigation`).
	 */
	public $title;



	/**
	 * Initialize a menu.
	 *
	 * @param int|string $slug    A menu slug, the term ID of the menu, the full name from the admin
	 *                            menu, the slug of theregistered location or nothing. Passing nothing
	 *                            is good if you only have one menu. Timber will grab what it finds.
	 * @param array      $options An array of options, right now only `depth` is supported
	 */
	public function __construct( $slug = 0, $options = array() ) {
		$menu_id = false;
		$locations = get_nav_menu_locations();

		$this->set_options((array)$options);

		if ( $slug != 0 && is_numeric($slug) ) {
			$menu_id = $slug;
		} else if ( is_array($locations) && count($locations) ) {
			$menu_id = $this->get_menu_id_from_locations($slug, $locations);
		} else if ( $slug === false ) {
			$menu_id = false;
		}
		if ( !$menu_id ) {
			$menu_id = $this->get_menu_id_from_terms($slug);
		}
		if ( $menu_id ) {
			$this->init($menu_id);
		} else {
			$this->init_as_page_menu();
		}
	}

	/**
	 * @internal
	 * @param int $menu_id
	 */
	protected function init( $menu_id ) {
		$menu = wp_get_nav_menu_items($menu_id);
		if ( $menu ) {
			_wp_menu_item_classes_by_context($menu);
			if ( is_array($menu) ) {
				$menu = self::order_children($menu);
				$menu = self::strip_to_depth_limit($menu);
			}
			$this->items = $menu;
			$menu_info = wp_get_nav_menu_object($menu_id);
			$this->import($menu_info);
			$this->ID = $this->term_id;
			$this->id = $this->term_id;
			$this->title = $this->name;
		}
	}

	/**
	 * @internal
	 * @param mixed $options
	 */
	protected function set_options ($options) {
		// Set any important options
		$this->depth = (isset($options['depth']) ? (int)$options['depth'] : -1);
		$this->raw_options = $options; // for future enhancements?
	}

	/**
	 * @internal
	 */
	protected function init_as_page_menu() {
		$menu = get_pages(array('sort_column' => 'menu_order'));
		if ( $menu ) {
			foreach ( $menu as $mi ) {
				$mi->__title = $mi->post_title;
			}
			_wp_menu_item_classes_by_context($menu);
			if ( is_array($menu) ) {
				$menu = self::order_children($menu);
			}
			$this->items = $menu;
		}
	}

	/**
	 * @internal
	 * @param string $slug
	 * @param array $locations
	 * @return integer
	 */
	protected function get_menu_id_from_locations( $slug, $locations ) {
		if ( $slug === 0 ) {
			$slug = $this->get_menu_id_from_terms($slug);
		}
		if ( is_numeric($slug) ) {
			$slug = array_search($slug, $locations);
		}
		if ( isset($locations[$slug]) ) {
			$menu_id = $locations[$slug];
			if ( function_exists('wpml_object_id_filter') ) {
				$menu_id = wpml_object_id_filter($locations[$slug], 'nav_menu');
			}

			return $menu_id;
		}
	}

	/**
	 * @internal
	 * @param int $slug
	 * @return int
	 */
	protected function get_menu_id_from_terms( $slug = 0 ) {
		if ( !is_numeric($slug) && is_string($slug) ) {
			//we have a string so lets search for that
			$menu = get_term_by('slug', $slug, 'nav_menu');
			if ( $menu ) {
				return $menu->term_id;
			}
			$menu = get_term_by('name', $slug, 'nav_menu');
			if ( $menu ) {
				return $menu->term_id;
			}
		}
		$menus = get_terms('nav_menu', array('hide_empty' => true));
		if ( is_array($menus) && count($menus) ) {
			if ( isset($menus[0]->term_id) ) {
				return $menus[0]->term_id;
			}
		}
		return 0;
	}

	/**
	 * Find a parent menu item in a set of menu items.
	 *
	 * @api
	 * @param array $menu_items An array of menu items.
	 * @param int   $parent_id  The parent ID to look for.
	 * @return \Timber\MenuItem|bool A menu item. False if no parent was found.
	 */
	public function find_parent_item_in_menu( $menu_items, $parent_id ) {
		foreach ( $menu_items as &$item ) {
			if ( $item->ID == $parent_id ) {
				return $item;
			}
		}
	}

	/**
	 * @internal
	 * @param array $items
	 * @return array
	 */
	protected function order_children( $items ) {
		$index = array();
		$menu = array();
		foreach ( $items as $item ) {
			if ( isset($item->title) ) {
				// Items from WordPress can come with a $title property which conflicts with methods
				$item->__title = $item->title;
				unset($item->title);
			}
			if ( isset($item->ID) ) {
				if ( is_object($item) && get_class($item) == 'WP_Post' ) {
					$old_menu_item = $item;
					$item = new $this->PostClass($item);
				}
				$menu_item = $this->create_menu_item($item);
				if ( isset($old_menu_item) ) {
					$menu_item->import_classes($old_menu_item);
				}
				$index[$item->ID] = $menu_item;
			}
		}
		foreach ( $index as $item ) {
			if ( isset($item->menu_item_parent) && $item->menu_item_parent && isset($index[$item->menu_item_parent]) ) {
				$index[$item->menu_item_parent]->add_child($item);
			} else {
				$menu[] = $item;
			}
		}
		return $menu;
	}

	/**
	 * @internal
	 * @param object $item the WP menu item object to wrap
	 * @return mixed an instance of the user-configured $MenuItemClass
	 */
	protected function create_menu_item($item) {
		return new $this->MenuItemClass($item);
	}

	/**
	 * @internal
	 * @param array $menu
	 */
	protected function strip_to_depth_limit ($menu, $current = 1) {
		$depth = (int)$this->depth; // Confirms still int.
		if ($depth <= 0) {
			return $menu;
		}

		foreach ($menu as &$currentItem) {
			if ($current == $depth) {
				$currentItem->children = false;
				continue;
			}

			$currentItem->children = self::strip_to_depth_limit($currentItem->children, $current + 1);
		}

		return $menu;
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
	public function get_items() {
		if ( is_array( $this->items ) ) {
			return $this->items;
		}

		return array();
	}
}
