<?php
class TimberMenuTest extends WP_UnitTestCase {

	function testBlankMenu() {
		$struc = '/%postname%/';
		global $wp_rewrite;
    	$wp_rewrite->set_permalink_structure($struc);
    	$wp_rewrite->flush_rules();
		update_option( 'permalink_structure', $struc );
		flush_rewrite_rules(true);
		$this->_createTestMenu();
		$menu = new TimberMenu();
		$nav_menu = wp_nav_menu( array( 'echo' => false ) );
		$this->assertEquals( 2, count( $menu->get_items() ) );
		$items = $menu->get_items();
		$item = $items[0];
		$this->assertEquals( 'home', $item->slug() );
		$this->assertFalse( $item->is_external() );
		$struc = get_option( 'permalink_structure' );
		$this->assertEquals( 'http://example.org/home', $item->permalink() );
		$this->assertEquals( 'http://example.org/home', $item->get_permalink() );
		$this->assertEquals( 'http://example.org/home', $item->url );
		$this->assertEquals( 'http://example.org/home', $item->link() );
		$this->assertEquals( '/home', $item->path() );
	}

	function testMenuTwig() {
		$struc = '/%postname%/';
		global $wp_rewrite;
    	$wp_rewrite->set_permalink_structure($struc);
    	$wp_rewrite->flush_rules();
		update_option( 'permalink_structure', $struc );
		$context = Timber::get_context();
		$this->_createTestMenu();
		$this->go_to(home_url('/child-page'));
		$context['menu'] = new TimberMenu();
		$str = Timber::compile( 'assets/child-menu.twig', $context );
		$str = preg_replace('/\s+/', '', $str);
		$this->assertEquals('<ulclass="navnavbar-nav"><li><ahref="http://example.org/home"class="has-children">Home</a><ulclass="dropdown-menu"role="menu"><li><ahref="http://example.org/child-page">ChildPage</a></li></ul><li><ahref="http://upstatement.com"class="no-children">Upstatement</a></ul>', $str);
	}

	function testMenuTwigWithClasses(){
		$struc = '/%postname%/';
		global $wp_rewrite;
    	$wp_rewrite->set_permalink_structure($struc);
    	$wp_rewrite->flush_rules();
		update_option( 'permalink_structure', $struc );
		$this->_createTestMenu();
		$this->go_to(home_url('/home'));
		$context = Timber::get_context();

		

		$context['menu'] = new TimberMenu();
		$str = Timber::compile( 'assets/menu-classes.twig', $context );
		$str = trim($str);
		$this->assertContains('current_page_item', $str);
		$this->assertContains('current-menu-item', $str);
		$this->assertContains('menu-item-object-page', $str);
		$this->assertNotContains('foobar', $str);
		
	}

	function testMenuItemLink() {
		$struc = '/%postname%/';
		update_option( 'permalink_structure', $struc );
		$this->_createTestMenu();
		$menu = new TimberMenu();
		$nav_menu = wp_nav_menu( array( 'echo' => false ) );
		$this->assertEquals( 2, count( $menu->get_items() ) );
		$items = $menu->get_items();
		$item = $items[1];
		$this->assertTrue( $item->is_external() );
		$struc = get_option( 'permalink_structure' );
		$this->assertEquals( 'http://upstatement.com', $item->permalink() );
		$this->assertEquals( 'http://upstatement.com', $item->get_permalink() );
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

	// function testMenuAtLocation(){
	//  register_nav_menu('theme-menu-location', 'A Nice Place to Put a Menu');
	//  $menu = $this->_createTestMenu();
	//  print_r($menu);
	// }

	function _createTestMenu() {
		global $wpdb;
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
		$post = new TimberPost($child_menu_item);
		$menu_items[] = $child_menu_item;
		foreach ( $menu_items as $object_id ) {
			$query = "INSERT INTO $wpdb->term_relationships (object_id, term_taxonomy_id, term_order) VALUES ($object_id, $menu_id, 0);";
			$wpdb->query( $query );
			update_post_meta( $object_id, 'tobias', 'funke' );
		}
		$menu_items_count = count( $menu_items );
		$wpdb->query( "UPDATE $wpdb->term_taxonomy SET count = $menu_items_count WHERE taxonomy = 'nav_menu'; " );
		$results = $wpdb->get_results( "SELECT * FROM $wpdb->term_taxonomy" );
		return $menu_term;
	}

}
