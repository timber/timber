<?php

class TimberMenu extends TimberCore {

    var $items = null;
    var $name = null;
    var $ID = null;

    function __construct($slug = 0) {
        $locations = get_nav_menu_locations();
        if ($slug === 0){
            reset($locations);
            $slug = key($locations);
        }
        if (is_numeric($slug)){
            $slug = array_search($slug, $locations);
        }
        if (isset($locations[$slug])) {
            $menu_id = $locations[$slug];
            $menu = wp_get_nav_menu_items($menu_id);
            $menu = self::order_children($menu);
            $this->items = $menu;
            $menu_info = wp_get_nav_menu_object($menu_id);
            $this->import($menu_info);
            $this->ID = $this->term_id;
        } else {
            WPHelper::error_log("Sorry, the menu you were looking for wasn't found ('".$slug."'). Here's what Timber did find:");
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
        _wp_menu_item_classes_by_context($items);
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
        $this->import_classes($data);
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

    function import_classes($data){
        $this->class = trim(implode(' ', $data->classes));
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