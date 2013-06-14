<?php
	
	class TimberMenu {

		function __construct($slug){
			$menu = wp_get_nav_menu_object($slug);
			$menu = wp_get_nav_menu_items($menu);
			return $menu;
		}
	}