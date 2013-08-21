<?php

class TimberMenu extends TimberCore {

    var $items = null;

    function __construct($slug) {
        $menu_id = $slug;
        if (!is_numeric($slug)){
            $locations = get_nav_menu_locations();
            $menu_id = wp_get_nav_menu_object($locations[$slug]);
        }
        if (isset($locations[$slug]) || is_numeric($menu_id)) {
            $menu = wp_get_nav_menu_items($menu_id);
            $menu = self::order_children($menu);
            $this->items = $menu;
        } else {
            WPHelper::error_log("Sorry, the menu you were looking for wasn't found ('".$slug."'). Here's what Timber did find:");
            WPHelper::error_log($locations);
        }
        return null;
    }

    function find_parent_item_in_menu($menu_items, $parent_id) {
        foreach ($menu_items as &$item) {
            if ($item->ID == $parent_id) {
                return $item;
            }
        }
        return null;
    }

    function order_children($items) {
        $index = array();
        $menu = array();
        foreach($items as $item) {
            $index[$item->ID] = new TimberMenuItem($item);
        }
        foreach($index as $item) {
            if($item->menu_item_parent) {
                $index[$item->menu_item_parent]->add_child($item);
            } else {
                $menu[] = $item;
            }
        }
        return $menu;
    }

    function get_items() {
        if (is_array($this->items)) {
            return $this->items;
        }
        return array();
    }
}

class TimberMenuItem extends TimberCore {

    var $children;

    function __construct($data) {
        $this->import($data);
    }

    function get_link() {
        return $this->get_path();
    }

    function name() {
        return $this->post_title;
    }

    function slug() {
        return $this->post_name;
    }

    function get_path() {
        return $this->url_to_path($this->url);
    }

    function add_child($item) {
        if (!isset($this->children)) {
            $this->children = array();
        }
        $this->children[] = $item;
    }

    function get_children() {
        if (isset($this->children)) {
            return $this->children;
        }
        return false;
    }

    /* Aliases */
    function link(){
        return $this->get_link();
    }

    function permalink(){
        return $this->get_link();
    }

    function get_permalink(){
        return $this->get_link();
    }
}