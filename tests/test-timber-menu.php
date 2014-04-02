<?php
	class TimberMenuTest extends WP_UnitTestCase {

		function testBlankMenu(){
			global $wpdb;
			$menu_term = wp_insert_term('Menu One', 'nav_menu');
			$menu_id = $menu_term['term_id'];
			$menu_items = array();
			$menu_items[] = wp_insert_post(
				array(
					'post_title' => 'Home',
					'post_status' => 'publish',
					'post_name' => 'home',
					'post_type' => 'nav_menu_item'
				)
			);
			$menu_items[] = wp_insert_post(
				array(
					'post_title' => 'Upstatement',
					'post_status' => 'publish',
					'post_name' => '',
					'post_type' => 'nav_menu_item'
				)
			);
			foreach($menu_items as $object_id){
				$wpdb->query("INSERT INTO $wpdb->term_relationships (object_id, term_taxonomy_id, term_order) VALUES ($object_id, $menu_id, 0);");
			}
			$menu_items_count = count($menu_items);
			$wpdb->query("UPDATE $wpdb->term_taxonomy SET count = $menu_items_count WHERE taxonomy = 'nav_menu'; ");
			$results = $wpdb->get_results("SELECT * FROM $wpdb->term_taxonomy");
			$menu = new TimberMenu();
			$nav_menu = wp_nav_menu(array('echo' => false));
			$this->assertEquals(2, count($menu->get_items()));
			$this->assertEquals(count($menu->get_items()), substr_count($nav_menu, '<li'));
		}

	}
