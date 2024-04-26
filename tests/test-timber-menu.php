<?php

class CustomMenuItemClass extends Timber\MenuItem
{
}

/**
 * @group menus-api
 */
class TestTimberMenu extends Timber_UnitTestCase
{
    public const MENU_NAME = 'Menu One';

    public static function _createTestMenu()
    {
        register_post_type('dummy-post-type', [
            'has_archive' => true,
        ]);

        $menu_term = wp_insert_term(self::MENU_NAME, 'nav_menu');
        $menu_id = $menu_term['term_id'];
        $menu_items = [];

        // Page
        $parent_page = wp_insert_post([
            'post_title' => 'Home',
            'post_status' => 'publish',
            'post_name' => 'home',
            'post_type' => 'page',
            'menu_order' => 1,
        ]);

        // Menu item
        $menu_items[] = $parent_id = wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-object-id' => $parent_page,
            'menu-item-object' => 'page',
            'menu-item-type' => 'post_type',
            'menu-item-status' => 'publish',
        ]);

        // Menu item
        $menu_items[] = $link_id = wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-title' => 'Upstatement',
            'menu-item-url' => 'https://upstatement.com',
            'menu-item-status' => 'publish',
            'menu-item-target' => '_blank',
        ]);

        /* make a child page */
        // Page
        $child_id = wp_insert_post([
            'post_title' => 'Child Page',
            'post_status' => 'publish',
            'post_name' => 'child-page',
            'post_type' => 'page',
            'menu_order' => 3,
        ]);

        $menu_items[] = $child_menu_item_id = wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-object-id' => $child_id,
            'menu-item-object' => 'page',
            'menu-item-type' => 'post_type',
            'menu-item-status' => 'publish',
            'menu-item-parent-id' => $parent_id,
        ]);

        /* make a grandchild page */
        $grandchild_id = wp_insert_post([
            'post_title' => 'Grandchild Page',
            'post_status' => 'publish',
            'post_name' => 'grandchild-page',
            'post_type' => 'page',
        ]);
        $menu_items[] = $grandchild_menu_item_id = wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-object-id' => $grandchild_id,
            'menu-item-object' => 'page',
            'menu-item-type' => 'post_type',
            'menu-item-status' => 'publish',
            'menu-item-parent-id' => $child_menu_item_id,
            'menu-item-position' => 100,
        ]);

        /* make another grandchild page */
        $other_grandchild_id = wp_insert_post([
            'post_title' => 'Other Grandchild Page',
            'post_status' => 'publish',
            'post_name' => 'other-grandchild-page',
            'post_type' => 'page',
        ]);
        $menu_items[] = $other_grandchild_menu_item = wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-object-id' => $other_grandchild_id,
            'menu-item-object' => 'page',
            'menu-item-type' => 'post_type',
            'menu-item-status' => 'publish',
            'menu-item-parent-id' => $child_menu_item_id,
            'menu-item-position' => 101,
        ]);

        $menu_items[] = $root_url_link_id = wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-title' => 'Root Home',
            'menu-item-url' => '/',
            'menu-item-status' => 'publish',
            'menu-item-position' => 4,
        ]);

        $menu_items[] = $link_id = wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-title' => 'People',
            'menu-item-url' => '#people',
            'menu-item-status' => 'publish',
            'menu-item-position' => 6,
        ]);

        $menu_items[] = $link_id = wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-title' => 'More People',
            'menu-item-url' => 'http://example.org/#people',
            'menu-item-status' => 'publish',
            'menu-item-position' => 7,
        ]);

        $menu_items[] = $link_id = wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-title' => 'Manual Home',
            'menu-item-url' => 'http://example.org',
            'menu-item-status' => 'publish',
            'menu-item-position' => 8,
        ]);

        $some_category = wp_insert_term('Some Category', 'category');
        $menu_items[] = $link_id = wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-object-id' => $some_category['term_id'],
            'menu-item-object' => 'category',
            'menu-item-type' => 'taxonomy',
            'menu-item-status' => 'publish',
        ]);

        $menu_items[] = wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-object' => 'dummy-post-type',
            'menu-item-type' => 'post_type_archive',
            'menu-item-status' => 'publish',
        ]);

        foreach ($menu_items as $menu_item_id) {
            update_post_meta($menu_item_id, 'tobias', 'funke');
        }
        return $menu_term;
    }

    public static function buildMenu($name, $items)
    {
        $menu_term = wp_insert_term($name, 'nav_menu');
        $i = 0;
        foreach ($items as $item) {
            if ($item->type == 'link') {
                wp_update_nav_menu_item($menu_term['term_id'], 0, [
                    'menu-item-title' => '',
                    'menu-item-url' => $item->link,
                    'menu-item-status' => 'publish',
                    'menu-item-position' => $i,
                ]);
            }
            $i++;
        }
        return $menu_term;
    }

    public function registerNavMenus($locations)
    {
        $theme = new Timber\Theme();

        update_option('theme_mods_' . $theme->slug, [
            'nav_menu_locations' => $locations,
        ]);

        register_nav_menus(
            [
                'header-menu' => 'Header Menu',
                'extra-menu' => 'Extra Menu',
                'bonus' => 'The Bonus',
            ]
        );
    }

    public static function _createSimpleMenu($name = 'My Menu')
    {
        $menu_term = wp_insert_term($name, 'nav_menu');
        $parent_page = wp_insert_post([
            'post_title' => 'Home',
            'post_status' => 'publish',
            'post_name' => 'home',
            'post_type' => 'page',
            'menu_order' => 1,
        ]);
        $menu_item_id = wp_update_nav_menu_item($menu_term['term_id'], 0, [
            'menu-item-object-id' => $parent_page,
            'menu-item-object' => 'page',
            'menu-item-type' => 'post_type',
            'menu-item-status' => 'publish',
        ]);
        update_post_meta($menu_item_id, 'flood', 'molasses');

        return $menu_term;
    }

    public static function setPermalinkStructure($struc = '/%postname%/')
    {
        global $wp_rewrite;
        $wp_rewrite->set_permalink_structure($struc);
        $wp_rewrite->flush_rules();
        update_option('permalink_structure', $struc);
        flush_rewrite_rules(true);
    }

    public function testMenuCache()
    {
        $term = self::_createTestMenu();
        $term_id = $term['term_id'];
        $called = 0;

        $cache_menu = function ($menu, $args) use (&$called) {
            $called++;
            set_transient('menu_cache_key', $menu, DAY_IN_SECONDS);
            return $menu;
        };
        $get_cached_menu = function ($menu, $args) {
            $menu = get_transient('menu_cache_key');
            if (is_string($menu)) {
                return $menu;
            }
            return null;
        };

        add_filter('wp_nav_menu', $cache_menu, 10, 2);
        add_filter('pre_wp_nav_menu', $get_cached_menu, 10, 2);

        // trigger menu cache
        $menu_uncached = Timber::get_menu($term_id);
        $menu_cached = Timber::get_menu($term_id);

        remove_filter('wp_nav_menu', $cache_menu, 10, 2);
        remove_filter('pre_wp_nav_menu', $get_cached_menu, 10, 2);
        $this->assertEquals($menu_uncached, $menu_cached);
        $this->assertEquals($called, 1);
    }

    public function testBlankMenu()
    {
        self::setPermalinkStructure();
        self::_createTestMenu();
        $menu = Timber::get_menu();
        $nav_menu = wp_nav_menu([
            'echo' => false,
        ]);
        $this->assertGreaterThanOrEqual(3, count($menu->get_items()));
        $items = $menu->get_items();
        $item = $items[0];
        $this->assertEquals('home', $item->slug());
        $this->assertFalse($item->is_external());
        $struc = get_option('permalink_structure');
        $this->assertEquals('http://example.org/home/', $item->link());
        $this->assertEquals('/home/', $item->path());
    }

    public function testTrailingSlashesOrNot()
    {
        self::setPermalinkStructure();
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
        $menu = $this->buildMenu('Blanky', $items);
        $menu = Timber::get_menu($menu['term_id']);
        $items = $menu->get_items();
        $this->assertEquals('/', $items[0]->path());
        $this->assertEquals('/foo', $items[1]->path());
        $this->assertEquals('/bar/', $items[2]->path());
    }

    /**
     * @group menuThumbnails
     */
    public function testNavMenuThumbnailsWithInitializedMenu()
    {
        add_theme_support('thumbnails');
        self::setPermalinkStructure();

        $menu_term = self::_createTestMenu();
        $menu = Timber::get_menu($menu_term['term_id']);
        $menu_items = $menu->items;

        // Add attachment to post
        $pid = $menu->items[0]->object_id;
        $iid = TestTimberImage::get_attachment($pid);
        add_post_meta($pid, '_thumbnail_id', $iid, true);

        // Lets confirm this post has a thumbnail on it!
        $post = Timber::get_post($pid);
        $this->assertEquals('http://example.org/wp-content/uploads/' . date('Y/m') . '/arch.jpg', $post->thumbnail());

        $nav_menu = Timber::get_menu($menu_term['term_id']);

        $str = '{{ menu.items[0].ID }} - {{ menu.items[0].master_object.thumbnail.src }}';
        $result = Timber::compile_string($str, [
            'menu' => $nav_menu,
        ]);
        $this->assertEquals($menu_items[0]->ID . ' - http://example.org/wp-content/uploads/' . date('Y/m') . '/arch.jpg', $result);
    }

    public function testImportClasses()
    {
        $menu = self::_createSimpleMenu('Main Tester');
        $menu = Timber::get_menu('Main Tester');
        $items = $menu->get_items();
        $item = $items[0];
        $array = [
            'classes' => ['menu-test-class'],
        ];
        $item->import_classes($array);
        $this->assertContains('menu-test-class', $item->classes);
    }

    /**
     * Test the menu with applied filter in wp_nav_menu_objects
     */
    public function testNavMenuFilters()
    {
        self::_createTestMenu();

        $this->add_filter_temporarily('wp_nav_menu_objects', function ($menu_items) {
            // Be careful with indexes, they are strings here
            $menu_items['9']->current = true;
            $menu_items['9']->classes = array_merge((array) $menu_items[8]->classes, ['current-menu-item', 'my-custom-item-class']);
            return $menu_items;
        }, 2);

        $arguments = [
            'depth' => 1,
        ];
        $menu = Timber::get_menu('Menu One', $arguments);
        $menu_items = $menu->get_items();

        $this->assertTrue($menu_items[7]->current);
        $this->assertContains('current-menu-item', $menu_items[7]->classes);
        $this->assertContains('my-custom-item-class', $menu_items[7]->classes);
    }

    public function testMenuArgs()
    {
        $menu_arr = self::_createTestMenu();

        $defaults = [
            'depth' => 0,
            'menu' => '',
            'container' => 'div',
            'container_class' => '',
            'container_id' => '',
            'container_aria_label' => '',
            'menu_class' => 'menu',
            'menu_id' => '',
            'echo' => true,
            'fallback_cb' => 'wp_page_menu',
            'before' => '',
            'after' => '',
            'link_after' => '',
            'link_before' => '',
            'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
            'item_spacing' => 'preserve',
            'walker' => '',
            'theme_location' => '',
        ];

        // With no options set.
        $menu = Timber::get_menu($menu_arr['term_id']);
        $defaults['menu'] = $menu_arr['term_id'];
        $this->assertIsInt($menu->depth);
        $this->assertSame(0, $menu->depth);
        $this->assertIsArray($menu->raw_args);
        $this->assertIsObject($menu->args);
        $this->assertEquals((object) $defaults, $menu->args);

        // With Valid options set.
        $args = wp_parse_args([
            'depth' => 1,
        ], $defaults);
        $menu = Timber::get_menu('Menu One', $args);
        $args['menu'] = 'Menu One';
        $this->assertIsInt($menu->depth);
        $this->assertSame(1, $menu->depth);
        $this->assertIsArray($menu->raw_args);
        $this->assertEquals($args, $menu->raw_args);
        $this->assertIsObject($menu->args);
        $this->assertEquals((object) $args, $menu->args);

        // With invalid option set.
        $args = [
            'depth' => 'boogie',
        ];
        $menu = Timber::get_menu('Menu One', $args);
        $this->assertIsInt($menu->depth);
        $this->assertSame(0, $menu->depth);
    }

    public function testMenuOptions_Depth()
    {
        self::_createTestMenu();
        $arguments = [
            'depth' => 1,
        ];
        $menu = Timber::get_menu('Menu One', $arguments);

        // Confirm that none of them have "children" set.
        $items = $menu->get_items();
        foreach ($items as $item) {
            $this->assertSame(false, $item->children);
        }

        // Confirm two levels deep
        $arguments = [
            'depth' => 2,
        ];
        $menu = Timber::get_menu('Menu One', $arguments);
        foreach ($items as $item) {
            if ($item->children) {
                foreach ($item->children as $child) {
                    $this->assertSame(null, $child->children);
                }
            }
        }
    }

    public function testMenuItemLink()
    {
        self::setPermalinkStructure();
        $menu_arr = self::_createTestMenu();
        $menu = Timber::get_menu($menu_arr['term_id']);
        $nav_menu = wp_nav_menu([
            'echo' => false,
        ]);
        $this->assertGreaterThanOrEqual(3, count($menu->get_items()));
        $items = $menu->get_items();
        $item = $items[1];
        $struc = get_option('permalink_structure');
        $this->assertEquals('https://upstatement.com', $item->link());
        $this->assertEquals('https://upstatement.com', $item->url);
        $this->assertTrue($item->is_external());
    }

    public function testMenuOptionsInNavMenuCssClassFilter()
    {
        $term = self::_createTestMenu();
        $menu_id = $term['term_id'];

        $this->registerNavMenus([
            'secondary' => $menu_id,
        ]);

        $filter = function ($classes, $item, $args) {
            $this->assertSame(3, $args->depth);

            return $classes;
        };

        $this->add_filter_temporarily('nav_menu_css_class', $filter, 10, 3);

        Timber::get_menu($menu_id, [
            'depth' => 3,
        ]);
    }

    public function testMenuItemsFilter()
    {
        $term = self::_createTestMenu();
        $menu_id = $term['term_id'];

        $filter = function (array $items, WP_Term $menu) {
            return array_map(function ($item) {
                $item->classes[] = "test_{$item->ID}";
                return $item;
            }, $items);
        };

        $this->add_filter_temporarily('timber/menu/item_objects', $filter, 10, 2);

        $menu = Timber::get_menu($menu_id);
        $items = $menu->get_items();

        foreach ($items as $item) {
            $this->assertContains("test_{$item->ID}", $item->classes);
        }
    }

    public function testMenuItemIsTargetBlank()
    {
        $menu_arr = self::_createTestMenu();
        $menu = Timber::get_menu($menu_arr['term_id']);
        $items = $menu->get_items();

        // Menu item without _menu_item_target set
        $item = $items[0];
        $this->assertFalse($item->is_target_blank());

        // Menu item with _menu_item_target set to '_blank'
        $item = $items[1];
        $this->assertTrue($item->is_target_blank());

        // Menu item with _menu_item_target set to ''
        $item = $items[2];
        $this->assertFalse($item->is_target_blank());
    }

    public function testMenuTwigWithClasses()
    {
        self::setPermalinkStructure();
        self::_createTestMenu();
        $this->go_to(home_url('/home'));
        $context = Timber::context();
        $context['menu'] = Timber::get_menu();
        $str = Timber::compile('assets/menu-classes.twig', $context);
        $str = trim($str);
        $this->assertStringContainsString('current_page_item', $str);
        $this->assertStringContainsString('current-menu-item', $str);
        $this->assertStringContainsString('menu-item-object-page', $str);
        $this->assertStringNotContainsString('foobar', $str);
    }

    public function testCustomArchivePage()
    {
        self::setPermalinkStructure();
        $this->add_filter_temporarily('nav_menu_css_class', function ($classes, $menu_item) {
            if (trailingslashit($menu_item->url) == trailingslashit('http://example.org/gallery')) {
                $classes[] = 'current-page-item';
            }
            return $classes;
        }, 10, 2);
        global $wpdb;
        register_post_type(
            'gallery',
            [
                'labels' => [
                    'name' => __('Gallery'),
                    'singular_name' => __('Gallery'),
                ],
                'taxonomies' => ['post_tag'],
                'supports' => ['title', 'editor', 'thumbnail', 'revisions'],
                'public' => true,
                'has_archive' => true,
                'rewrite' => [
                    'slug' => 'gallery',
                ],
            ]
        );
        $menu = self::_createTestMenu();
        $menu_item_id = wp_insert_post([
            'post_title' => 'Gallery',
            'post_name' => 'gallery',
            'post_status' => 'publish',
            'post_type' => 'nav_menu_item',
            'menu_order' => -100,
        ]);
        update_post_meta($menu_item_id, '_menu_item_type', 'post_type_archive');
        update_post_meta($menu_item_id, '_menu_item_object', 'gallery');
        update_post_meta($menu_item_id, '_menu_item_menu_item_parent', 0);
        update_post_meta($menu_item_id, '_menu_item_object_id', 0);
        update_post_meta($menu_item_id, '_menu_item_url', '');
        $mid = $menu['term_id'];
        $query = "INSERT INTO $wpdb->term_relationships (object_id, term_taxonomy_id, term_order) VALUES ($menu_item_id, $mid, 0);";

        $wpdb->query($query);
        $this->go_to(home_url('/gallery'));
        $menu = Timber::get_menu();
        $this->assertContains('current-page-item', $menu->items[0]->classes);
    }

    public function testMenuItemTarget()
    {
        $menu_arr = self::_createTestMenu();
        $menu = Timber::get_menu($menu_arr['term_id']);
        $items = $menu->get_items();

        // Menu item without _menu_item_target set
        $item = $items[0];
        $this->assertEquals('_self', $item->target());

        // Menu item with _menu_item_target set to '_blank'
        $item = $items[1];
        $this->assertEquals('_blank', $item->target());

        // Menu item with _menu_item_target set to ''
        $item = $items[2];
        $this->assertEquals('_self', $item->target());
    }

    public function testMenuItemMetaAlt()
    {
        $menu_info = $this->_createSimpleMenu();
        $menu = Timber::get_menu($menu_info['term_id']);
        $item = $menu->items[0];
        $this->assertEquals('molasses', $item->meta('flood'));
    }

    public function testMenuItemMetaProperty()
    {
        $menu_arr = self::_createTestMenu();
        $menu = Timber::get_menu($menu_arr['term_id']);
        $items = $menu->get_items();
        $item = $items[0];
        $this->assertEquals('funke', $item->tobias);
        // There are dozens of us! DOZENS!!
        $this->assertGreaterThan(0, $item->id);
    }

    public function testMenuItemMeta()
    {
        $menu_info = $this->_createSimpleMenu();
        $menu = Timber::get_menu($menu_info['term_id']);
        $item = $menu->items[0];
        $this->assertEquals('molasses', $item->meta('flood'));
    }

    public function testMenuMetaSet()
    {
        $menu_arr = self::_createSimpleMenu('Tester');
        $menu = Timber::get_menu($menu_arr['term_id']);
        $items = $menu->get_items();
        $item = $items[0];
        $item->foo = 'bar';
        update_post_meta($item->ID, 'ziggy', 'stardust');
        $this->assertNotEquals($item->ID, $item->master_object->ID);
        $this->assertEquals('bar', $item->foo);
        $this->assertNotEquals('bar', $item->meta('foo'));
        $this->assertEquals('stardust', $item->meta('ziggy'));
        $this->assertSame('', $item->meta('asdfafds'));
    }

    public function testMenuMeta()
    {
        $term = self::_createTestMenu();
        $menu_id = $term['term_id'];
        add_term_meta($menu_id, 'nationality', 'Canadian');
        $menu = Timber::get_menu($menu_id);
        $string = Timber::compile_string('{{menu.meta("nationality")}}', [
            'menu' => $menu,
        ]);
        $this->assertEquals('Canadian', $string);
    }

    public function testMenuItemWithHash()
    {
        $menu_arr = self::_createTestMenu();
        $menu = Timber::get_menu($menu_arr['term_id']);
        $items = $menu->get_items();
        $item = $items[3];
        $this->assertEquals('#people', $item->link());
        $item = $items[4];
        $this->assertEquals('http://example.org/#people', $item->link());
        $this->assertEquals('/#people', $item->path());
    }

    public function testMenuHome()
    {
        $menu_arr = self::_createTestMenu();
        $menu = Timber::get_menu($menu_arr['term_id']);
        $items = $menu->get_items();
        $item = $items[2];
        $this->assertEquals('/', $item->link());
        $this->assertEquals('/', $item->path());

        $item = $items[5];
        $this->assertEquals('http://example.org', $item->link());
        //I'm unsure what the expected behavior should be here, so commenting-out for now.
        //$this->assertEquals('/', $item->path() );
    }

    public function testMenuLevels()
    {
        $menu_arr = self::_createTestMenu();
        $menu = Timber::get_menu($menu_arr['term_id']);
        $parent = $menu->items[0];
        $this->assertSame(0, $parent->level);
        $child = $parent->children[0];
        $this->assertSame(1, $child->level);
        $olderGrandchild = $child->children[0];
        $this->assertEquals('Grandchild Page', $olderGrandchild->title());
        $this->assertSame(2, $olderGrandchild->level);
        $youngerGrandchild = $child->children[1];
        $this->assertEquals('Other Grandchild Page', $youngerGrandchild->title());
        $this->assertSame(2, $youngerGrandchild->level);
    }

    public function testMenuLevelsChildren()
    {
        $menu_arr = self::_createTestMenu();
        $menu = Timber::get_menu($menu_arr['term_id']);
        $parent = $menu->items[0];
        $this->assertSame(0, $parent->level);
        $children = $parent->children();
        $this->assertSame(1, count($children));
        $this->assertEquals('Child Page', $children[0]->title());
    }

    public function testMenuName()
    {
        $menu_arr = self::_createTestMenu();
        $menu = Timber::get_menu($menu_arr['term_id']);
        $str = Timber::compile_string('{{menu.items[0].title}}', [
            'menu' => $menu,
        ]);
        $this->assertEquals('Home', $str);
        $str = Timber::compile_string('{{menu.items[0]}}', [
            'menu' => $menu,
        ]);
        $this->assertEquals('Home', $str);
    }

    public function testMenuLocations()
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

        $this->buildMenu('Froggy', $items);

        $built_menu = $this->buildMenu('Ziggy', $items);
        $built_menu_id = $built_menu['term_id'];

        $this->buildMenu('Zappy', $items);

        $this->registerNavMenus([
            'header-menu' => 0,
            'extra-menu' => $built_menu_id,
            'bonus' => 0,
        ]);

        $menu = Timber::get_menu('extra-menu');
        $this->assertEquals('Ziggy', $menu->name);
    }

    public function testConstructMenuByName()
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

        $this->buildMenu('Fancy Suit', $items);

        $menu = Timber::get_menu('Fancy Suit');
        $this->assertSame(3, count($menu->get_items()));
    }

    public function testConstructMenuBySlug()
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

        $this->buildMenu('Jolly Jeepers', $items);

        $menu = Timber::get_menu('jolly-jeepers');
        $this->assertSame(3, count($menu->get_items()));
    }

    public function testGetCurrentItem()
    {
        $items = [];
        $items[] = (object) [
            'type' => 'link',
            'link' => '/',
        ];
        $items[] = (object) [
            'type' => 'link',
            'link' => '/zazzy',
        ];
        $items[] = (object) [
            'type' => 'link',
            'link' => '/stuffy',
        ];

        $this->buildMenu('The Zazziest Menu', $items);

        $menu = Timber::get_menu('The Zazziest Menu');

        // force a specific MenuItem to be the current one,
        // and put it on the Zazz Train to Zazzville
        $menu->items[0]->current_item_ancestor = true;
        $menu->items[1]->current = true;

        $current = $menu->current_item();
        $this->assertEquals('/zazzy', $current->link());
    }

    public function testGetCurrentItemWithAncestor()
    {
        $items = [];
        $items[] = (object) [
            'type' => 'link',
            'link' => '/',
        ];
        $items[] = (object) [
            'type' => 'link',
            'link' => '/grandpa',
        ];
        $items[] = (object) [
            'type' => 'link',
            'link' => '/joe-shmoe',
        ];

        $this->buildMenu('Ancestry.com Main Menu', $items);

        $menu = Timber::get_menu('Ancestry.com Main Menu');

        // force a MenuItem of olde to be the current one,
        // and listen reverently to its stories
        $menu->items[1]->current_item_ancestor = true;

        $current = $menu->current_item();
        $this->assertEquals('/grandpa', $current->link());
    }

    public function testGetCurrentItemWithComplexAncestry()
    {
        $menu_arr = self::_createTestMenu();
        $menu = Timber::get_menu($menu_arr['term_id']);

        // pick a grandchild to inherit the great responsibility of current affairs
        $parent = $menu->items[0];
        $parent->current_item_ancestor = true;

        $child = $parent->children[0];
        $child->current_item_ancestor = true;

        $grandchild = $child->children[1];
        $grandchild->current = true;

        $current = $menu->current_item();
        $this->assertEquals($grandchild->link(), $current->link());
    }

    public function testGetCurrentItemAntiClimactic()
    {
        $menu_arr = self::_createTestMenu();
        $menu = Timber::get_menu($menu_arr['term_id']);

        // nothing marked as current
        // womp womp
        $this->assertFalse($menu->current_item());
    }

    public function testGetCurrentItemWithDepth()
    {
        $menu_arr = self::_createTestMenu();
        $menu = Timber::get_menu($menu_arr['term_id']);

        // pick a grandchild to inherit the great responsibility of current affairs
        $parent = $menu->items[0];
        $parent->current_item_ancestor = true;

        // although grandchild is current, we expect this one because of $depth
        $child = $parent->children[0];
        $child->current_item_ancestor = true;

        // mark grandchild as current, so when we get child back,
        // we can reason that the traversal was depth-limited
        $grandchild = $child->children[1];
        $grandchild->current = true;

        $current = $menu->current_item(2);
        $this->assertEquals($child->link(), $current->link());
    }

    public function testGetCurrentItemSequence()
    {
        // make sure we're not caching current_item too eagerly
        // when calling current_item with $depth
        $menu_arr = self::_createTestMenu();
        $menu = Timber::get_menu($menu_arr['term_id']);

        // we'll expect parent first, but expect grandchild on subsequent calls
        // with no arguments
        $parent = $menu->items[0];
        $parent->current_item_ancestor = true;

        $child = $parent->children[0];
        $child->current = true;

        $this->assertEquals(
            $parent->link(),
            $menu->current_item(1)->link()
        );
        $this->assertEquals(
            $child->link(),
            $menu->current_item()->link()
        );
    }

    public function testGetCurrentTopLevelItem()
    {
        $menu_arr = self::_createTestMenu();
        $menu = Timber::get_menu($menu_arr['term_id']);

        // we want this one
        $parent = $menu->items[0];
        $parent->current_item_ancestor = true;

        // although grandchild is current, we expect this one because of $depth
        $child = $parent->children[0];
        $child->current = true;

        $top = $menu->current_top_level_item();
        $this->assertEquals($parent->link(), $top->link());
    }

    /**
     * @issue https://github.com/timber/timber/issues/2576
     */
    public function testThemeLocationProperty()
    {
        $term = self::_createTestMenu();
        $menu_id = $term['term_id'];

        $this->registerNavMenus([
            'primary' => null,
            'secondary' => $menu_id,
        ]);

        $menu = Timber::get_menu($menu_id);

        $this->assertEquals('secondary', $menu->theme_location);

        // Test property access from menu item.
        $this->assertEquals($menu, $menu->items[0]->menu);
        $this->assertEquals('secondary', $menu->items[0]->menu->theme_location);
    }

    public function testThemeLocationAccessInNavMenuCssClassFilter()
    {
        $term = self::_createTestMenu();
        $menu_id = $term['term_id'];

        $this->registerNavMenus([
            'secondary' => $menu_id,
        ]);

        $filter = function ($classes, $item, $args) {
            if ('secondary' === $args->theme_location) {
                $classes[] = 'test-class';
            }

            return $classes;
        };

        $this->add_filter_temporarily('nav_menu_css_class', $filter, 10, 3);

        $menu = Timber::get_menu($menu_id);

        foreach ($menu->items as $item) {
            $this->assertContains('test-class', $item->classes);
        }
    }

    public function testCustomMenuItemClass()
    {
        $term = self::_createTestMenu();
        $menu_id = $term['term_id'];
        $menu_items = wp_get_nav_menu_items($menu_id);
        $tmis = [];

        foreach ($menu_items as $mi) {
            $tmi = CustomMenuItemClass::build($mi);
            array_push($tmis, $tmi);
        }

        $this->assertEquals($tmis[4]->post_title, 'People');
    }

    public function testMenuItemObjectProperty()
    {
        $term = self::_createTestMenu();
        $menu_id = $term['term_id'];

        $this->registerNavMenus([
            'secondary' => $menu_id,
        ]);

        $menu = Timber::get_menu($term['term_id']);
        $item = $menu->items[0];
        $object_id = (int) get_post_meta($item->ID, '_menu_item_object_id', true);

        $this->assertEquals($object_id, $item->object_id);
    }

    /*
     * Make sure we still get back nothing even though we have a fallback present
     */
    public function testMissingMenu()
    {
        $pg_1 = $this->factory->post->create([
            'post_type' => 'page',
            'post_title' => 'Foo Page',
            'menu_order' => 10,
        ]);
        $pg_2 = $this->factory->post->create([
            'post_type' => 'page',
            'post_title' => 'Bar Page',
            'menu_order' => 1,
        ]);
        $missing_menu = Timber::get_menu(14);
        $this->assertNull($missing_menu);
    }

    public function testMenuTwig()
    {
        self::setPermalinkStructure();
        $context = Timber::context();
        $menu_arr = self::_createTestMenu();
        $this->go_to(home_url('/child-page'));

        // To check if the filter is applied
        $this->add_filter_temporarily('nav_menu_item_title', function ($title, WP_Post $item) {
            if ($item->title !== 'Home') {
                return $title;
            }
            return 'Home Sweet ' . $title;
        }, 10, 2);

        $context['menu'] = Timber::get_menu($menu_arr['term_id']);
        $str = Timber::compile('assets/child-menu.twig', $context);
        $str = preg_replace('/\s+/', '', $str);
        $str = preg_replace('/\s+/', '', $str);
        $this->assertStringStartsWith('<ulclass="navnavbar-nav"><li><ahref="http://example.org/home/"class="has-children">HomeSweetHome</a><ulclass="dropdown-menu"role="menu"><li><ahref="http://example.org/child-page/">ChildPage</a></li></ul><li><ahref="https://upstatement.com"class="no-children">Upstatement</a><li><ahref="/"class="no-children">RootHome</a>', $str);
    }

    public function testMasterObject()
    {
        $menu = self::_createTestMenu();
        $menu_id = $menu['term_id'];

        $menu = Timber::get_menu($menu_id);
        $this->assertInstanceOf(Timber\Post::class, $menu->items[0]->master_object());
        $this->assertInstanceOf(Timber\Term::class, $menu->items[6]->master_object());
        $this->assertInstanceOf(WP_Post_Type::class, $menu->items[7]->master_object());
    }

    public function testMenuWalker()
    {
        $menu = self::_createTestMenu();
        $menu_id = $menu['term_id'];
        $args = [
            'menu_class' => 'my-unique-menu-class',
            'menu_id' => 'my-unique-menu-id',
            'container' => 'nav',
            'container_class' => 'my-unique-container-class',
            'container_id' => 'my-unique-container-id',
        ];
        $menu = Timber::get_menu($menu_id, $args);
        $args['menu'] = $menu_id;
        $args['echo'] = false;

        $nav_menu_wp = wp_nav_menu($args);
        // Remove this filter that prevents to render duplicate ids
        remove_filter('nav_menu_item_id', '_nav_menu_item_id_use_once', 10, 2);
        $nav_menu_timber = (string) $menu;

        $this->assertEquals($nav_menu_wp, $nav_menu_timber);
        $this->assertStringContainsString('class="my-unique-menu-class"', $nav_menu_timber);
        $this->assertStringContainsString('id="my-unique-menu-id"', $nav_menu_timber);
        $this->assertStringContainsString('class="my-unique-container-class"', $nav_menu_timber);
        $this->assertStringContainsString('id="my-unique-container-id"', $nav_menu_timber);
    }

    public function testMenuCanEdit()
    {
        self::_createTestMenu();

        $subscriber_id = $this->factory->user->create([
            'display_name' => 'Subscriber Sam',
            'user_login' => 'subsam',
            'role' => 'subscriber',
        ]);

        $menu = Timber::get_menu('Menu One');

        // Test admin role.
        wp_set_current_user(1);
        $this->assertTrue($menu->can_edit());

        // Test subscriber role.
        wp_set_current_user($subscriber_id);
        $this->assertFalse($menu->can_edit());

        wp_set_current_user(0);
    }

    public function testMenuItemCanEdit()
    {
        self::_createTestMenu();

        $subscriber_id = $this->factory->user->create([
            'display_name' => 'Subscriber Sam',
            'user_login' => 'subsam',
            'role' => 'subscriber',
        ]);

        $menu = Timber::get_menu('Menu One');
        $menu_items = $menu->get_items();

        // Test admin role.
        wp_set_current_user(1);
        $this->assertTrue($menu_items[0]->can_edit());

        // Test subscriber role.
        wp_set_current_user($subscriber_id);
        $this->assertFalse($menu_items[0]->can_edit());

        wp_set_current_user(0);
    }

    public function testWPObject()
    {
        $menu_id = self::_createTestMenu()['term_id'];
        $menu = Timber::get_menu($menu_id);

        $this->assertInstanceOf('WP_Term', $menu->wp_object());
    }

    public function testWPObjectMenuItem()
    {
        $menu_id = self::_createTestMenu()['term_id'];
        $menu = Timber::get_menu($menu_id);
        $item = $menu->get_items()[0];

        $this->assertInstanceOf('WP_Post', $item->wp_object());
    }
}
