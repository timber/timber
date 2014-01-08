<?php

class TimberMenu extends TimberCore {

    var $items = null;
    var $name = null;
    var $ID = null;

    function __construct($slug = 0) {
        $locations = get_nav_menu_locations();
        if ($slug != 0 && is_numeric($slug)){
            $menu_id = $slug;
        } else if (count($locations)){
            $menu_id = $this->get_menu_id_from_locations($slug, $locations);
        } else if ($slug === false){
            $menu_id = false;
        } else {
            $menu_id = $this->get_menu_id_from_terms($slug);
        }
        if ($menu_id){
            $this->init($menu_id);
        } else {
            TimberHelper::error_log("Sorry, the menu you were looking for wasn't found ('".$slug."'). Here's what Timber did find:");
        }
        return null;
    }

    private function init($menu_id){
        $menu = wp_get_nav_menu_items($menu_id);
        $menu = self::order_children($menu);
        $this->items = $menu;
        $menu_info = wp_get_nav_menu_object($menu_id);
        $this->import($menu_info);
        $this->ID = $this->term_id;
    }

    private function get_menu_id_from_locations($slug, $locations){
        if ($slug === 0){
            $slug = $this->get_menu_id_from_terms($slug);
        }
        if (is_numeric($slug)){
            $slug = array_search($slug, $locations);
        }
        if (isset($locations[$slug])) {
            return $locations[$slug];
        }
    }

    private function get_menu_id_from_terms($slug = 0){
        if (!is_numeric($slug) && is_string($slug)){
            //we have a string so lets search for that
            $menu_id = get_term_by('slug', $slug, 'nav_menu');
            if ($menu_id){
                return $menu_id;
            }
            $menu_id = get_term_by('name', $slug, 'nav_menu');
            if ($menu_id){
                return $menu_id;
            }
        }
        $menus = get_terms( 'nav_menu', array( 'hide_empty' => true ) );
        if (is_array($menus) && count($menus)){
            if (isset($menus[0]->term_id)){
                return $menus[0]->term_id;
            }
        }
        return 0;
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
            if($item->menu_item_parent && isset($index[$item->menu_item_parent])) {
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
    var $has_child_class = false;

    function __construct($data) {
        $this->import($data);
        $this->import_classes($data);
        if (isset($this->name)){
            $this->_name = $this->name;
        }
        $this->name = $this->name();
        $this->add_class('menu-item-'.$this->ID);
    }

    function add_class($class_name){
        $this->classes[] = $class_name;
        $this->class .= ' '.$class_name;
    }

    function name() {
        if (isset($this->title)){
            return $this->title;
        }
        return $this->_name;
    }

    function slug() {
        return $this->post_name;
    }

    function get_link() {
        return $this->url;
    }

    function get_path() {
        return TimberHelper::get_rel_url($this->url);
    }

    function add_child($item) {
        if (!$this->has_child_class){
            $this->add_class('menu-item-has-children');
        }
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

    function is_external(){
        if ($this->type != 'custom'){
            return false;
        }
        return TimberHelper::is_external($this->url);
    }

    /* Aliases */

    public function children(){
        return $this->get_children();
    }

    public function external(){
        return $this->is_external();
    }

    public function link(){
        return $this->get_link();
    }

    public function path(){
        return $this->get_path();
    }

    public function permalink(){
        return $this->get_link();
    }

    public function get_permalink(){
        return $this->get_link();
    }
}