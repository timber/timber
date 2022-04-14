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
	 * Initializes a pages menu.
	 *
	 * @api
	 *
	 * @param null  $menu Unused. Only here for compatibility with the Timber\Menu class.
	 * @param array $args Optional. Args for wp_list_pages().
	 *
	 * @return \Timber\PagesMenu
	 */
	public static function build( $menu, $args = [] ) : ?self {
		/**
		 * Default arguments from wp_list_pages() function.
		 *
		 * @see wp_list_pages()
		 */
		$defaults = [
			'depth'        => 0,
			'show_date'    => '',
			'date_format'  => get_option( 'date_format' ),
			'child_of'     => 0,
			'exclude'      => '',
			'title_li'     => __( 'Pages' ),
			'echo'         => 1,
			'authors'      => '',
			'sort_column'  => 'menu_order, post_title',
			'link_before'  => '',
			'link_after'   => '',
			'item_spacing' => 'preserve',
			'walker'       => '',
		];

		$args = wp_parse_args( $args, $defaults );

		if ( ! in_array( $args['item_spacing'], array( 'preserve', 'discard' ), true ) ) {
			// Invalid value, fall back to default.
			$args['item_spacing'] = $defaults['item_spacing'];
		}

		// Sanitize, mostly to keep spaces out.
		$args['exclude'] = preg_replace( '/[^0-9,]/', '', $args['exclude'] );

		// Allow plugins to filter an array of excluded pages (but don't put a nullstring into the array).
		$exclude_array = ( $args['exclude'] ) ? explode( ',', $args['exclude'] ) : array();

		/**
		 * Filters the array of pages to exclude from the pages list.
		 *
		 * @param string[] $exclude_array An array of page IDs to exclude.
		 */
		$args['exclude'] = implode( ',', apply_filters( 'wp_list_pages_excludes', $exclude_array ) );

		$args['hierarchical'] = 0;

		$pages_menu = new static( $args );
		$pages_menu->init_pages_menu();

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
		apply_filters( 'wp_nav_menu', serialize( $pages_menu ), $args );

		return $pages_menu;
	}

	/**
	 * @internal
	 */
	protected function __construct( $args ) {
		$this->args = (object) $args;
		$this->depth = (int) $this->args->depth;
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
				$menu_items = $this->strip_to_depth_limit( $menu_items );
			}

			$this->items = $menu_items;
		}
	}
}
