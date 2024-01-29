<?php

use Timber\Factory\PagesMenuFactory;

class MyPagesMenu extends Timber\PagesMenu
{
}

/**
 * @group factory
 * @group menus-api
 */
class TestPagesMenuFactory extends Timber_UnitTestCase
{
    public function testPagesMenuClassFilter()
    {
        $this->factory->term->create([
            'name' => 'Main Menu',
            'taxonomy' => 'nav_menu',
        ]);

        $factory = new PagesMenuFactory();

        $this->add_filter_temporarily('timber/pages_menu/class', function () {
            return MyPagesMenu::class;
        });

        $this->assertInstanceOf(MyPagesMenu::class, $factory->from_pages());
    }
}
