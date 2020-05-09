<?php

use Timber\Menu;
use Timber\Factory\MenuFactory;

class MyMenu extends Menu {}

/**
 * @group factory
 * @group menus-api
 */
class TestMenuFactory extends Timber_UnitTestCase {
	public function testMenuFromTermId() {
		$id = $this->factory->term->create([
			'name'     => 'Main Menu',
			'taxonomy' => 'nav_menu',
		]);

		$factory = new MenuFactory();

		$this->assertInstanceOf(Menu::class, $factory->from($id));
	}

	public function testGetMenuFromInvalidId() {
		$factory = new MenuFactory();

		$this->assertFalse( $factory->from(9999999) );
	}

	public function testGetMenuFromIdString() {
		$id = $this->factory->term->create([
			'name'     => 'Main Menu',
			'taxonomy' => 'nav_menu',
		]);

		$factory = new MenuFactory();

		$this->assertInstanceOf(Menu::class, $factory->from("$id"));
	}

	public function testGetMenuFromName() {
		$id = $this->factory->term->create([
			'name'     => 'Main Menu',
			'taxonomy' => 'nav_menu',
		]);

		$factory = new MenuFactory();

		$this->assertInstanceOf(Menu::class, $factory->from('Main Menu'));
	}

	public function testGetMenuFromSlug() {
		$id = $this->factory->term->create([
			'name'     => 'Main Menu',
			'taxonomy' => 'nav_menu',
		]);

		$factory = new MenuFactory();

		$this->assertInstanceOf(Menu::class, $factory->from('main-menu'));
	}

	public function testFromTimberMenuObject() {
		$id = $this->factory->term->create([
			'name'     => 'Main Menu',
			'taxonomy' => 'nav_menu',
		]);


		$factory = new MenuFactory();
		$term    = $factory->from($id);

		$this->assertInstanceOf(Menu::class, $factory->from($term));
	}

	public function testFromWpTermObject() {
		$id = $this->factory->term->create([
			'name'     => 'Main Menu',
			'taxonomy' => 'nav_menu',
		]);


		$factory = new MenuFactory();
		$term    = get_term($id);

		$this->assertInstanceOf(Menu::class, $factory->from($term));
	}
}
