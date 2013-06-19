<?php
	
	class TimberMenu extends TimberCore {

		var $items = null;

		function __construct($slug){
			//$menu = wp_get_nav_menu_object($slug);
			$locations = get_nav_menu_locations();
			if (isset($locations[$slug])){
				$menu = wp_get_nav_menu_object($locations[$slug]);
				$menu = wp_get_nav_menu_items($menu);
				$this->items = $menu;
			}
			return null;
		}

		function get_items(){
			if (is_array($this->items)){
				return $this->items;
			}
			return array();
		}
	}