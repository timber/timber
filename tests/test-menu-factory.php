<?php

use Timber\Factory\MenuFactory;
use Timber\Menu;

class MyMenu extends Menu
{
}

/**
 * @group factory
 * @group menus-api
 */
class TestMenuFactory extends Timber_UnitTestCase
{
    public function testMenuFromTermId()
    {
        $id = $this->factory->term->create([
            'name' => 'Main Menu',
            'taxonomy' => 'nav_menu',
        ]);

        $factory = new MenuFactory();

        $this->assertInstanceOf(Menu::class, $factory->from($id));
    }

    public function testGetMenuFromInvalidId()
    {
        $factory = new MenuFactory();

        $this->assertNull($factory->from(9999999));
    }

    public function testGetMenuFromNavMenuTerms()
    {
        $this->create_menu_from_posts([
            [
                'post_title' => 'Home',
                'post_status' => 'publish',
                'post_name' => 'home',
                'post_type' => 'page',
                'menu_order' => 1,
            ],
        ]);

        $factory = new MenuFactory();

        $this->assertInstanceOf(Menu::class, $factory->from(0));
    }

    public function testGetMenuFromIdString()
    {
        $id = $this->factory->term->create([
            'name' => 'Main Menu',
            'taxonomy' => 'nav_menu',
        ]);

        $factory = new MenuFactory();

        $menu = $factory->from("$id");
        $menu_by = $factory->from_id($id);

        $this->assertInstanceOf(Menu::class, $menu);
        $this->assertEquals($id, $menu->args->menu);
        $this->assertInstanceOf(Menu::class, $menu_by);
        $this->assertEquals($id, $menu_by->args->menu);
    }

    public function testGetMenuFromName()
    {
        $name = 'Main Menu';
        $id = $this->factory->term->create([
            'name' => $name,
            'taxonomy' => 'nav_menu',
        ]);

        $factory = new MenuFactory();

        $menu = $factory->from($name);
        $menu_by = $factory->from_name($name);

        $this->assertInstanceOf(Menu::class, $menu);
        $this->assertEquals($name, $menu->args->menu);
        $this->assertInstanceOf(Menu::class, $menu_by);
        $this->assertEquals($name, $menu_by->args->menu);
    }

    public function testGetMenuFromSlug()
    {
        $slug = 'main-menu';
        $id = $this->factory->term->create([
            'name' => 'Main Menu',
            'taxonomy' => 'nav_menu',
        ]);

        $factory = new MenuFactory();

        $menu = $factory->from_slug($slug);
        $menu_by = $factory->from($slug);

        $this->assertInstanceOf(Menu::class, $menu);
        $this->assertEquals($slug, $menu->args->menu);
        $this->assertInstanceOf(Menu::class, $menu_by);
        $this->assertEquals($slug, $menu_by->args->menu);
    }

    public function testGetMenuFromLocation()
    {
        $location = 'custom';
        $id = $this->factory->term->create([
            'name' => 'Main Menu',
            'taxonomy' => 'nav_menu',
        ]);

        // Set up our new custom menu location.
        register_nav_menu('custom', 'Custom nav location');
        $locations = [
            $location => $id,
        ];
        set_theme_mod('nav_menu_locations', $locations);

        $factory = new MenuFactory();

        $menu = $factory->from($location);
        $menu_by = $factory->from_location($location);

        $this->assertInstanceOf(Menu::class, $menu);
        $this->assertEquals($location, $menu->args->theme_location);
        $this->assertInstanceOf(Menu::class, $menu_by);
        $this->assertEquals($location, $menu_by->args->theme_location);
    }

    public function testFromTimberMenuObject()
    {
        $id = $this->factory->term->create([
            'name' => 'Main Menu',
            'taxonomy' => 'nav_menu',
        ]);

        $factory = new MenuFactory();
        $term = get_term($id, 'nav_menu');
        $menu = $factory->from($term);

        $this->assertInstanceOf(Menu::class, $menu);
        $this->assertEquals($term, $menu->args->menu);
    }

    public function testFromTimberMenuGarbageInGarbageOut()
    {
        $factory = new MenuFactory();
        $this->assertNull($factory->from(null));
    }

    public function testFromWpTermObject()
    {
        $id = $this->factory->term->create([
            'name' => 'Main Menu',
            'taxonomy' => 'nav_menu',
        ]);

        $factory = new MenuFactory();
        $term = get_term($id);

        $this->assertInstanceOf(Menu::class, $factory->from($term));
    }

    public function testMenuClassFilter()
    {
        $id = $this->factory->term->create([
            'name' => 'Main Menu',
            'taxonomy' => 'nav_menu',
        ]);

        $factory = new MenuFactory();

        $this->add_filter_temporarily('timber/menu/class', fn () => MyMenu::class);

        $this->assertTrue(MyMenu::class === ($factory->from($id) !== null ? $factory->from($id)::class : self::class));
    }

    public function testMenuClassMapFilter()
    {
        $id = $this->factory->term->create([
            'name' => 'Main Menu',
            'taxonomy' => 'nav_menu',
        ]);

        $factory = new MenuFactory();

        // Set up our new custom menu location.
        register_nav_menu('custom', 'Custom nav location');
        set_theme_mod('nav_menu_locations', [
            'custom' => $id,
        ]);

        $this->add_filter_temporarily('timber/menu/classmap', fn () => [
            'custom' => MyMenu::class,
        ]);

        $this->assertTrue(MyMenu::class === ($factory->from($id) !== null ? $factory->from($id)::class : self::class));
    }

    /**
     * @issue https://github.com/timber/timber/issues/2576
     */
    public function testGetMenuLocation()
    {
        $id = $this->factory->term->create([
            'name' => 'Main Menu',
            'taxonomy' => 'nav_menu',
        ]);
        $locations = [
            'primary' => $id,
            'secondary' => null,
        ];
        set_theme_mod('nav_menu_locations', $locations);
        $factory = new MenuFactory();
        $location = Timber::get_menu_location(get_term($id));
        $this->assertSame('primary', $location);

        $location = Timber::get_menu_location($id);
        $this->assertSame('primary', $location);
    }
}
