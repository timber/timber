<?php

use Timber\Factory\MenuItemFactory;
use Timber\MenuItem;
use Timber\Timber;

/**
 * @group factory
 * @group menus-api
 */
class TestMenuItemFactory extends Timber_UnitTestCase {
	public function testMenuFromId() {
		$page_id = wp_insert_post([
				'post_title' => 'Home',
				'post_status' => 'publish',
				'post_name' => 'home',
				'post_type' => 'page',
				'menu_order' => 1,
		]);
		$item_id = wp_insert_post([
				'post_title' => '',
				'post_status' => 'publish',
				'post_type' => 'nav_menu_item',
		]);
		update_post_meta( $item_id, '_menu_item_object_id', $page_id );
		update_post_meta( $item_id, '_menu_item_type', 'post_type' );
		update_post_meta( $item_id, '_menu_item_object', 'page' );
		update_post_meta( $item_id, '_menu_item_menu_item_parent', 0 );
		update_post_meta( $item_id, '_menu_item_url', '' );

		$menu_term = wp_insert_term( 'Main Menu', 'nav_menu' );

		$this->add_menu_item($menu_term['term_id'], [$item_id]);

		$menu = Timber::get_menu($menu_term['term_id']);
		$factory = new MenuItemFactory();

		$this->assertInstanceOf(MenuItem::class, $factory->from($item_id, $menu));
  }
}