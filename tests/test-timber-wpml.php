<?php

class TestTimberWPML extends Timber_UnitTestCase {

	function testWPMLMenu() {
		$items = array();
		$items[] = (object) array('type' => 'link', 'link' => '/');
		$items[] = (object) array('type' => 'link', 'link' => '/foo');
		$items[] = (object) array('type' => 'link', 'link' => '/bar/');

		TestTimberMenu::buildMenu('Froggy', $items);

		$built_menu = TestTimberMenu::buildMenu('Ziggy', $items);
		$built_menu_id = $built_menu['term_id'];

		TestTimberMenu::buildMenu('Zappy', $items);
		$theme = new TimberTheme();
		$data = array('nav_menu_locations' => array('header-menu' => 0, 'extra-menu' => $built_menu_id, 'bonus' => 0));
		update_option('theme_mods_'.$theme->slug, $data);
		register_nav_menus(
		    array(
		    	'header-menu' => 'Header Menu',
				'extra-menu' => 'Extra Menu',
				'bonus' => 'The Bonus'
		    )
		);
		$menu = new TimberMenu('extra-menu');
		$this->assertEquals('Ziggy', $menu->name);
	}

}
