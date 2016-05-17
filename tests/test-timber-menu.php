<?php

class TestTimberMenu extends Timber_UnitTestCase {

	function testBlankMenu() {
		self::setPermalinkStructure();
		$this->_createTestMenu();
		$menu = new TimberMenu();
		$nav_menu = wp_nav_menu( array( 'echo' => false ) );
		$this->assertGreaterThanOrEqual( 3, count( $menu->get_items() ) );
		$items = $menu->get_items();
		$item = $items[0];
		$this->assertEquals( 'home', $item->slug() );
		$this->assertFalse( $item->is_external() );
		$struc = get_option( 'permalink_structure' );
		$this->assertEquals( 'http://example.org/home/', $item->link() );
		$this->assertEquals( '/home/', $item->path() );
	}

	function testTrailingSlashesOrNot() {
		self::setPermalinkStructure();
		$items = array();
		$items[] = (object) array('type' => 'link', 'link' => '/');
		$items[] = (object) array('type' => 'link', 'link' => '/foo');
		$items[] = (object) array('type' => 'link', 'link' => '/bar/');
		$mid = $this->buildMenu('Blanky', $items);
		$menu = new TimberMenu($mid);
		$items = $menu->get_items();
		$this->assertEquals('/', $items[0]->path());
		$this->assertEquals('/foo', $items[1]->path());
		$this->assertEquals('/bar/', $items[2]->path());
	}

	function testPagesMenu() {
		$pg_1 = $this->factory->post->create( array( 'post_type' => 'page', 'post_title' => 'Foo Page', 'menu_order' => 10 ) );
		$pg_2 = $this->factory->post->create( array( 'post_type' => 'page', 'post_title' => 'Bar Page', 'menu_order' => 1 ) );
		$page_menu = new TimberMenu();
		$this->assertEquals( 2, count( $page_menu->items ) );
		$this->assertEquals( 'Bar Page', $page_menu->items[0]->title() );
		$this->_createTestMenu();
		//make sure other menus are still more powerful
		$menu = new TimberMenu();
		$this->assertGreaterThanOrEqual( 3, count( $menu->get_items() ) );
	}

	function testPagesMenuWithFalse() {
		$pg_1 = $this->factory->post->create( array( 'post_type' => 'page', 'post_title' => 'Foo Page', 'menu_order' => 10 ) );
		$pg_2 = $this->factory->post->create( array( 'post_type' => 'page', 'post_title' => 'Bar Page', 'menu_order' => 1 ) );
		$page_menu = new TimberMenu();
		$this->assertEquals( 2, count( $page_menu->items ) );
		$this->assertEquals( 'Bar Page', $page_menu->items[0]->title() );
		$this->_createTestMenu();
		//make sure other menus are still more powerful
		$menu = new TimberMenu(false);
		$this->assertGreaterThanOrEqual( 3, count( $menu->get_items() ) );
	}

	/*
	 * Make sure we still get back nothing even though we have a fallback present
	 */
	function testMissingMenu() {
		$pg_1 = $this->factory->post->create( array( 'post_type' => 'page', 'post_title' => 'Foo Page', 'menu_order' => 10 ) );
		$pg_2 = $this->factory->post->create( array( 'post_type' => 'page', 'post_title' => 'Bar Page', 'menu_order' => 1 ) );
		$missing_menu = new TimberMenu( 14 );
		$this->assertTrue( empty( $missing_menu->items ) );
	}

	function testMenuTwig() {
		self::setPermalinkStructure();
		$context = Timber::get_context();
		$this->_createTestMenu();
		$this->go_to( home_url( '/child-page' ) );
		$context['menu'] = new TimberMenu();
		$str = Timber::compile( 'assets/child-menu.twig', $context );
		$str = preg_replace( '/\s+/', '', $str );
		$str = preg_replace( '/\s+/', '', $str );
		$this->assertStringStartsWith( '<ulclass="navnavbar-nav"><li><ahref="http://example.org/home/"class="has-children">Home</a><ulclass="dropdown-menu"role="menu"><li><ahref="http://example.org/child-page/">ChildPage</a></li></ul><li><ahref="http://upstatement.com"class="no-children">Upstatement</a><li><ahref="/"class="no-children">RootHome</a>', $str );
	}

	function testMenuTwigWithClasses() {
		self::setPermalinkStructure();
		$this->_createTestMenu();
		$this->go_to( home_url( '/home' ) );
		$context = Timber::get_context();
		$context['menu'] = new TimberMenu();
		$str = Timber::compile( 'assets/menu-classes.twig', $context );
		$str = trim( $str );
		$this->assertContains( 'current_page_item', $str );
		$this->assertContains( 'current-menu-item', $str );
		$this->assertContains( 'menu-item-object-page', $str );
		$this->assertNotContains( 'foobar', $str );
	}

	function testMenuItemLink() {
		self::setPermalinkStructure();
		$this->_createTestMenu();
		$menu = new TimberMenu();
		$nav_menu = wp_nav_menu( array( 'echo' => false ) );
		$this->assertGreaterThanOrEqual( 3, count( $menu->get_items() ) );
		$items = $menu->get_items();
		$item = $items[1];
		$this->assertTrue( $item->external() );
		$struc = get_option( 'permalink_structure' );
		$this->assertEquals( 'http://upstatement.com', $item->url );
		$this->assertEquals( 'http://upstatement.com', $item->link() );
	}

	function testMenuMeta() {
		$this->_createTestMenu();
		$menu = new TimberMenu();
		$items = $menu->get_items();
		$item = $items[0];
		$this->assertEquals( 'funke', $item->tobias );
		$this->assertGreaterThan( 0, $item->id );
	}

	function testMenuItemWithHash() {
		$this->_createTestMenu();
		$menu = new TimberMenu();
		$items = $menu->get_items();
		$item = $items[3];
		$this->assertEquals( '#people', $item->link() );
		$item = $items[4];
		$this->assertEquals( 'http://example.org/#people', $item->link() );
		$this->assertEquals( '/#people', $item->path() );
	}

	function testMenuHome() {
		$this->_createTestMenu();
		$menu = new TimberMenu();
		$items = $menu->get_items();
		$item = $items[2];
		$this->assertEquals( '/', $item->link() );
		$this->assertEquals( '/', $item->path() );

		$item = $items[5];
		$this->assertEquals( 'http://example.org', $item->link() );
		//I'm unsure what the expected behavior should be here, so commenting-out for now.
		//$this->assertEquals('/', $item->path() );
	}

	function buildMenu($name, $items) {
		$menu_term = wp_insert_term( $name, 'nav_menu' );
		$menu_items = array();
		$i = 0;
		foreach($items as $item) {
			if ($item->type == 'link') {
				$pid = wp_insert_post(array('post_title' => '', 'post_status' => 'publish', 'post_type' => 'nav_menu_item', 'menu_order' => $i));
				update_post_meta( $pid, '_menu_item_type', 'custom' );
				update_post_meta( $pid, '_menu_item_object_id', $pid );
				update_post_meta( $pid, '_menu_item_url', $item->link );
				update_post_meta( $pid, '_menu_item_xfn', '' );
				update_post_meta( $pid, '_menu_item_menu_item_parent', 0 );
				$menu_items[] = $pid;
			}
			$i++;
		}
		$this->insertIntoMenu($menu_term['term_id'], $menu_items);
		return $menu_term;
	}

	function _createSimpleMenu( $name = 'My Menu' ) {
		$menu_term = wp_insert_term( $name, 'nav_menu' );
		$menu_items = array();
		$parent_page = wp_insert_post(
			array(
				'post_title' => 'Home',
				'post_status' => 'publish',
				'post_name' => 'home',
				'post_type' => 'page',
				'menu_order' => 1
			)
		);
		$parent_id = wp_insert_post( array(
				'post_title' => '',
				'post_status' => 'publish',
				'post_type' => 'nav_menu_item'
			) );
		update_post_meta( $parent_id, '_menu_item_type', 'post_type' );
		update_post_meta( $parent_id, '_menu_item_object', 'page' );
		update_post_meta( $parent_id, '_menu_item_menu_item_parent', 0 );
		update_post_meta( $parent_id, '_menu_item_object_id', $parent_page );
		update_post_meta( $parent_id, '_menu_item_url', '' );
		update_post_meta( $parent_id, 'flood', 'molasses' );
		$menu_items[] = $parent_id;
		$this->insertIntoMenu($menu_term['term_id'], $menu_items);
		return $menu_term;
	}

	function _createTestMenu() {
		$menu_term = wp_insert_term( 'Menu One', 'nav_menu' );
		$menu_id = $menu_term['term_id'];
		$menu_items = array();
		$parent_page = wp_insert_post(
			array(
				'post_title' => 'Home',
				'post_status' => 'publish',
				'post_name' => 'home',
				'post_type' => 'page',
				'menu_order' => 1
			)
		);
		$parent_id = wp_insert_post( array(
				'post_title' => '',
				'post_status' => 'publish',
				'post_type' => 'nav_menu_item'
			) );
		update_post_meta( $parent_id, '_menu_item_type', 'post_type' );
		update_post_meta( $parent_id, '_menu_item_object', 'page' );
		update_post_meta( $parent_id, '_menu_item_menu_item_parent', 0 );
		update_post_meta( $parent_id, '_menu_item_object_id', $parent_page );
		update_post_meta( $parent_id, '_menu_item_url', '' );
		$menu_items[] = $parent_id;
		$link_id = wp_insert_post(
			array(
				'post_title' => 'Upstatement',
				'post_status' => 'publish',
				'post_type' => 'nav_menu_item',
				'menu_order' => 2
			)
		);

		$menu_items[] = $link_id;
		update_post_meta( $link_id, '_menu_item_type', 'custom' );
		update_post_meta( $link_id, '_menu_item_object_id', $link_id );
		update_post_meta( $link_id, '_menu_item_url', 'http://upstatement.com' );
		update_post_meta( $link_id, '_menu_item_xfn', '' );
		update_post_meta( $link_id, '_menu_item_menu_item_parent', 0 );

		/* make a child page */
		$child_id = wp_insert_post( array(
				'post_title' => 'Child Page',
				'post_status' => 'publish',
				'post_name' => 'child-page',
				'post_type' => 'page',
				'menu_order' => 3,
			) );
		$child_menu_item = wp_insert_post( array(
				'post_title' => '',
				'post_status' => 'publish',
				'post_type' => 'nav_menu_item',
			) );
		update_post_meta( $child_menu_item, '_menu_item_type', 'post_type' );
		update_post_meta( $child_menu_item, '_menu_item_menu_item_parent', $parent_id );
		update_post_meta( $child_menu_item, '_menu_item_object_id', $child_id );
		update_post_meta( $child_menu_item, '_menu_item_object', 'page' );
		update_post_meta( $child_menu_item, '_menu_item_url', '' );
		$post = new TimberPost( $child_menu_item );
		$menu_items[] = $child_menu_item;

		/* make a grandchild page */
		$grandchild_id = wp_insert_post( array(
				'post_title' => 'Grandchild Page',
				'post_status' => 'publish',
				'post_name' => 'grandchild-page',
				'post_type' => 'page',
				'menu_order' => 100,
			) );
		$grandchild_menu_item = wp_insert_post( array(
				'post_title' => '',
				'post_status' => 'publish',
				'post_type' => 'nav_menu_item',
			) );
		update_post_meta( $grandchild_menu_item, '_menu_item_type', 'post_type' );
		update_post_meta( $grandchild_menu_item, '_menu_item_menu_item_parent', $child_menu_item );
		update_post_meta( $grandchild_menu_item, '_menu_item_object_id', $grandchild_id );
		update_post_meta( $grandchild_menu_item, '_menu_item_object', 'page' );
		update_post_meta( $grandchild_menu_item, '_menu_item_url', '' );
		$post = new TimberPost( $grandchild_menu_item );
		$menu_items[] = $grandchild_menu_item;

		$root_url_link_id = wp_insert_post(
			array(
				'post_title' => 'Root Home',
				'post_status' => 'publish',
				'post_type' => 'nav_menu_item',
				'menu_order' => 4
			)
		);

		$menu_items[] = $root_url_link_id;
		update_post_meta( $root_url_link_id, '_menu_item_type', 'custom' );
		update_post_meta( $root_url_link_id, '_menu_item_object_id', $root_url_link_id );
		update_post_meta( $root_url_link_id, '_menu_item_url', '/' );
		update_post_meta( $root_url_link_id, '_menu_item_xfn', '' );
		update_post_meta( $root_url_link_id, '_menu_item_menu_item_parent', 0 );

		$link_id = wp_insert_post(
			array(
				'post_title' => 'People',
				'post_status' => 'publish',
				'post_type' => 'nav_menu_item',
				'menu_order' => 6
			)
		);

		$menu_items[] = $link_id;
		update_post_meta( $link_id, '_menu_item_type', 'custom' );
		update_post_meta( $link_id, '_menu_item_object_id', $link_id );
		update_post_meta( $link_id, '_menu_item_url', '#people' );
		update_post_meta( $link_id, '_menu_item_xfn', '' );
		update_post_meta( $link_id, '_menu_item_menu_item_parent', 0 );

		$link_id = wp_insert_post(
			array(
				'post_title' => 'More People',
				'post_status' => 'publish',
				'post_type' => 'nav_menu_item',
				'menu_order' => 7
			)
		);

		$menu_items[] = $link_id;
		update_post_meta( $link_id, '_menu_item_type', 'custom' );
		update_post_meta( $link_id, '_menu_item_object_id', $link_id );
		update_post_meta( $link_id, '_menu_item_url', 'http://example.org/#people' );
		update_post_meta( $link_id, '_menu_item_xfn', '' );
		update_post_meta( $link_id, '_menu_item_menu_item_parent', 0 );

		$link_id = wp_insert_post(
			array(
				'post_title' => 'Manual Home',
				'post_status' => 'publish',
				'post_type' => 'nav_menu_item',
				'menu_order' => 8
			)
		);

		$menu_items[] = $link_id;
		update_post_meta( $link_id, '_menu_item_type', 'custom' );
		update_post_meta( $link_id, '_menu_item_object_id', $link_id );
		update_post_meta( $link_id, '_menu_item_url', 'http://example.org' );
		update_post_meta( $link_id, '_menu_item_xfn', '' );
		update_post_meta( $link_id, '_menu_item_menu_item_parent', 0 );

		$this->insertIntoMenu($menu_id, $menu_items);
		return $menu_term;
	}

	function insertIntoMenu($menu_id, $menu_items) {
		global $wpdb;
		foreach ( $menu_items as $object_id ) {
			$query = "INSERT INTO $wpdb->term_relationships (object_id, term_taxonomy_id, term_order) VALUES ($object_id, $menu_id, 0);";
			$wpdb->query( $query );
			update_post_meta( $object_id, 'tobias', 'funke' );
		}
		$menu_items_count = count( $menu_items );
		$wpdb->query( "UPDATE $wpdb->term_taxonomy SET count = $menu_items_count WHERE taxonomy = 'nav_menu'; " );
	}

	static function setPermalinkStructure( $struc = '/%postname%/' ) {
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( $struc );
		$wp_rewrite->flush_rules();
		update_option( 'permalink_structure', $struc );
		flush_rewrite_rules( true );
	}

	function testCustomArchivePage() {
		self::setPermalinkStructure();
		add_filter( 'nav_menu_css_class', function( $classes, $menu_item ) {
				if ( trailingslashit( $menu_item->link() ) == trailingslashit( 'http://example.org/gallery' ) ) {
					$classes[] = 'current-page-item';
				}
				return $classes;
			}, 10, 2 );
		global $wpdb;
		register_post_type( 'gallery',
			array(
				'labels' => array(
					'name' => __( 'Gallery' ),
					'singular_name' => __( 'Gallery' )
				),
				'taxonomies' => array( 'post_tag' ),
				'supports' => array( 'title', 'editor', 'thumbnail', 'revisions' ),
				'public' => true,
				'has_archive' => true,
				'rewrite' => array( 'slug' => 'gallery' ),
			)
		);
		$menu = $this->_createTestMenu();
		$menu_item_id = wp_insert_post( array(
				'post_title' => 'Gallery',
				'post_name' => 'gallery',
				'post_status' => 'publish',
				'post_type' => 'nav_menu_item',
				'menu_order' => -100,
			) );
		update_post_meta( $menu_item_id, '_menu_item_type', 'post_type_archive' );
		update_post_meta( $menu_item_id, '_menu_item_object', 'gallery' );
		update_post_meta( $menu_item_id, '_menu_item_menu_item_parent', 0 );
		update_post_meta( $menu_item_id, '_menu_item_object_id', 0 );
		update_post_meta( $menu_item_id, '_menu_item_url', '' );
		$mid = $menu['term_id'];
		$query = "INSERT INTO $wpdb->term_relationships (object_id, term_taxonomy_id, term_order) VALUES ($menu_item_id, $mid, 0);";

		$wpdb->query( $query );
		$this->go_to( home_url( '/gallery' ) );
		$menu = new TimberMenu();
		$this->assertContains( 'current-page-item', $menu->items[0]->classes );
	}

	function testMenuLevels() {
		$this->_createTestMenu();
		$menu = new TimberMenu();
		$parent = $menu->items[0];
		$this->assertEquals(0, $parent->level);
		$child = $parent->children[0];
		$this->assertEquals(1, $child->level);
		$grandchild = $child->children[0];
		$this->assertEquals('Grandchild Page', $grandchild->title());
		$this->assertEquals(2, $grandchild->level);
	}

	function testMenuLevelsChildren() {
		$this->_createTestMenu();
		$menu = new TimberMenu();
		$parent = $menu->items[0];
		$this->assertEquals(0, $parent->level);
		$children = $parent->children();
		$this->assertEquals(1, count($children));
		$this->assertEquals('Child Page', $children[0]->title());
	}

	function testMenuItemMeta() {
		$menu_info = $this->_createSimpleMenu();
		$menu = new TimberMenu($menu_info['term_id']);
		$item = $menu->items[0];
		$this->assertEquals('molasses', $item->meta('flood'));
	}

	function testMenuName() {
		$this->_createTestMenu();
		$menu = new TimberMenu();
		$str = Timber::compile_string('{{menu.items[0].title}}', array('menu' => $menu));
		$this->assertEquals('Home', $str);
		$str = Timber::compile_string('{{menu.items[0]}}', array('menu' => $menu));
		$this->assertEquals('Home', $str);
	}

	function testMenuLocations() {
		$items = array();
		$items[] = (object) array('type' => 'link', 'link' => '/');
		$items[] = (object) array('type' => 'link', 'link' => '/foo');
		$items[] = (object) array('type' => 'link', 'link' => '/bar/');

		$this->buildMenu('Froggy', $items);

		$built_menu = $this->buildMenu('Ziggy', $items);
		$built_menu_id = $built_menu['term_id'];

		$this->buildMenu('Zappy', $items);
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

	function testConstructMenuByName() {
		$items = array();
		$items[] = (object) array('type' => 'link', 'link' => '/');
		$items[] = (object) array('type' => 'link', 'link' => '/foo');
		$items[] = (object) array('type' => 'link', 'link' => '/bar/');

		$this->buildMenu('Fancy Suit', $items);

		$menu = new TimberMenu('Fancy Suit');
		$this->assertEquals( 3, count($menu->get_items()) );
	}

	function testConstructMenuBySlug() {
		$items = array();
		$items[] = (object) array('type' => 'link', 'link' => '/');
		$items[] = (object) array('type' => 'link', 'link' => '/foo');
		$items[] = (object) array('type' => 'link', 'link' => '/bar/');

		$this->buildMenu('Jolly Jeepers', $items);

		$menu = new TimberMenu('jolly-jeepers');
		$this->assertEquals( 3, count($menu->get_items()) );
	}

}
