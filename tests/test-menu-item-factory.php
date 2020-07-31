<?php

use Timber\Factory\MenuItemFactory;
use Timber\MenuItem;
use Timber\Timber;

class MyMenuItem extends MenuItem {}

/**
 * @group factory
 * @group menus-api
 */
class TestMenuItemFactory extends Timber_UnitTestCase {
	public function testMenuFromId() {
		// Destructure the result into the menu WP_Term instance
		// and the *first* item_id
		[
			'term' => $menu_term,
			'item_ids' => [$item_id],
		] = $this->create_menu_from_posts([
			[
				'post_title' => 'Home',
				'post_status' => 'publish',
				'post_name' => 'home',
				'post_type' => 'page',
				'menu_order' => 1,
			]
		]);

		$menu = Timber::get_menu($menu_term['term_id']);
		$factory = new MenuItemFactory();

		$this->assertInstanceOf(MenuItem::class, $factory->from($item_id, $menu));
	}

	public function testMenuFromPost() {
		// Destructure the result into the menu WP_Term instance
		// and the *first* item_id
		[
			'term' => $menu_term,
			'item_ids' => [$item_id],
		] = $this->create_menu_from_posts([
			[
				'post_title' => 'Home',
				'post_status' => 'publish',
				'post_name' => 'home',
				'post_type' => 'page',
				'menu_order' => 1,
			]
		]);

		$menu = Timber::get_menu($menu_term['term_id']);
		$factory = new MenuItemFactory();

		$post = get_post($item_id);

		$this->assertInstanceOf(MenuItem::class, $factory->from($post, $menu));
  }
}