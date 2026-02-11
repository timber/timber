<?php

namespace Timber\Tests\Integration;

use Mantle\Testing\Attributes\PermalinkStructure;
use Mantle\Testing\Concerns\Refresh_Database;
use PHPUnit\Framework\Attributes\Group;
use Timber\ImageHelper;
use Timber\Tests\Image\ImageTest;
use Timber\Tests\MenuTest;
use Timber\Tests\TimberIntegrationTestCase;
use Timber\Theme;
use Timber\Timber;
use Timber\URLHelper;

#[Group('integrations')]
#[Group('wpml')]
class WPMLTest extends TimberIntegrationTestCase
{
    use Refresh_Database;

    public function set_up()
    {
        if (!\defined('ICL_LANGUAGE_CODE')) {
            $this->markTestSkipped('WPML plugin is not loaded');
        }
        parent::set_up();
    }

    public function testFileSystemToURLWithWPML()
    {
        $this->add_filter_temporarily('home_url', fn ($url, $path) => 'http://example2.org/en' . $path, 10, 2);

        $image = $this->copyImageToUploads();

        $url = URLHelper::file_system_to_url($image);
        $this->assertStringEndsWith('://example2.org/wp-content/uploads/' . \date('Y/m') . '/arch.jpg', $url);
    }

    public function testFileSystemToURLWithWPMLPrefix()
    {
        // Mock the WPML "Directory"
        $this->add_filter_temporarily('home_url', fn ($url, $path) => 'http://example.org/en' . $path, 10, 2);

        $image = $this->copyImageToUploads();
        $url = URLHelper::file_system_to_url($image);
        $this->assertEquals('http://example.org/wp-content/uploads/' . \date('Y/m') . '/arch.jpg', $url);
    }

    public function testWPMLurlRemote()
    {
        // this test replicates the url issue caused by the WPML language identifier in the url
        // However, WPML can't be installed with composer so this test mocks the WPML plugin

        // WPML uses a filter to alter the home_url
        // @todo this appears to be operating on a path, rather than a URL, causing:
        // Error loading /srv/www/wordpress-trunk/public_html/src/en/wp-content/uploads/external/fc990091d1d3ef80591db58450e4dc09.jpg
        $home_url_filter = (fn ($url) => \str_replace('example.org/', 'example.org/en/', $url));
        $this->add_filter_temporarily('home_url', $home_url_filter, -10, 4);

        $img = 'https://raw.githubusercontent.com/timber/timber/master/tests/assets/arch-2night.jpg';
        // test with a local and external file
        $resized = ImageHelper::resize($img, 50, 50);

        // make sure the base url has not been duplicated (https://github.com/timber/timber/issues/405)
        $this->assertLessThanOrEqual(1, \substr_count($resized, 'example.org'));
        // make sure the image has been resized
        $resized = URLHelper::url_to_file_system($resized);
        $this->assertTrue(ImageTest::checkSize($resized, 50, 50), 'image should be resized');
    }

    public function testWPMLurlLocal()
    {
        // this test replicates the url issue caused by the WPML language identifier in the url
        // However, WPML can't be installed with composer so this test mocks the WPML plugin

        // WPML uses a filter to alter the home_url
        $home_url_filter = (fn ($url) => $url . '/en');
        $this->add_filter_temporarily('home_url', $home_url_filter, -10, 4);

        // test with a local and external file
        $img = 'arch.jpg';
        $img = $this->copyImageToUploads($img);

        $resized = ImageHelper::resize($img, 50, 50);

        // make sure the base url has not been duplicated (https://github.com/timber/timber/issues/405)
        $this->assertLessThanOrEqual(1, \substr_count($resized, 'example.org'));
        // make sure the image has been resized
        $resized = URLHelper::url_to_file_system($resized);
        $this->assertTrue(ImageTest::checkSize($resized, 50, 50), 'image should be resized');
    }

    #[PermalinkStructure('/%postname%/')]
    public function testWPMLMenu()
    {
        $menu_arr = MenuTest::_createTestMenu();
        $home_slug = $menu_arr['home_slug'];
        $menu = Timber::get_menu();
        $nav_menu = \wp_nav_menu([
            'echo' => false,
        ]);
        $this->assertGreaterThanOrEqual(3, \count($menu->get_items()));
        $items = $menu->get_items();
        $item = $items[0];
        $this->assertEquals($home_slug, $item->slug());
        $this->assertFalse($item->is_external());
        $this->assertEquals('http://example.org/' . $home_slug . '/', $item->link());
        $this->assertEquals('/' . $home_slug . '/', $item->path());
    }

    public function testWPMLMenu2()
    {
        $items = [];
        $items[] = (object) [
            'type' => 'link',
            'link' => '/',
        ];
        $items[] = (object) [
            'type' => 'link',
            'link' => '/foo',
        ];
        $items[] = (object) [
            'type' => 'link',
            'link' => '/bar/',
        ];

        MenuTest::buildMenu('Froggy', $items);

        $built_menu = MenuTest::buildMenu('Ziggy', $items);
        $built_menu_id = $built_menu['term_id'];
        $built_menu_name = $built_menu['name'];

        MenuTest::buildMenu('Zappy', $items);
        $theme = new Theme();
        $data = [
            'nav_menu_locations' => [
                'header-menu' => 0,
                'extra-menu' => $built_menu_id,
                'bonus' => 0,

            ],
        ];
        \update_option('theme_mods_' . $theme->slug, $data);
        \register_nav_menus(
            [
                'header-menu' => 'Header Menu',
                'extra-menu' => 'Extra Menu',
                'bonus' => 'The Bonus',
            ]
        );
        $menu = Timber::get_menu('extra-menu');
        $this->assertEquals($built_menu_name, $menu->name);
    }
}
