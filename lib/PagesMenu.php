<?php

namespace Timber;

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
class PagesMenu extends Menu {
	/**
	 * @internal
	 */
	protected function __construct( $args ) {
		$this->args = $args;
	}

	/**
	 * Initializes a pages menu.
	 *
	 * @api
	 *
	 * @param null  $menu Unused. Only here for compatibility with the Timber\Menu class.
	 * @param array $args Optional. Args for get_pages().
	 *
	 * @see get_pages()
	 * @return \Timber\PagesMenu
	 */
	public static function build( $menu, $args = [] ) : ?self {
		$pages_menu = new static( $args );
		$pages_menu->init_pages_menu();

		return $pages_menu;
	}

	/**
	 * Inits pages menu.
	 */
	protected function init_pages_menu() {
		$menu_items = get_pages( $this->args );

		if ( $menu_items ) {
			$menu_items = array_map( 'wp_setup_nav_menu_item', $menu_items );

			_wp_menu_item_classes_by_context( $menu_items );

			if ( is_array( $menu_items ) ) {
				$menu_items = $this->convert_menu_items( $menu_items );
				$menu_items = $this->order_children( $menu_items );
			}

			$this->items = $menu_items;
		}
	}
}
