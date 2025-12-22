<?php

namespace Timber\Tests\Factory;

use PHPUnit\Framework\Attributes\Group;
use Timber\Factory\PagesMenuFactory;
use Timber\PagesMenu;
use Timber\Tests\TimberIntegrationTestCase;

class MyPagesMenu extends PagesMenu
{
}

#[Group('factory')]
#[Group('menus-api')]
class PagesMenuFactoryTest extends TimberIntegrationTestCase
{
    public function testPagesMenuClassFilter()
    {
        static::factory()->term->create([
            'name' => 'Main Menu',
            'taxonomy' => 'nav_menu',
        ]);

        $factory = new PagesMenuFactory();

        $this->add_filter_temporarily('timber/pages_menu/class', fn () => MyPagesMenu::class);

        $this->assertInstanceOf(MyPagesMenu::class, $factory->from_pages());
    }
}
