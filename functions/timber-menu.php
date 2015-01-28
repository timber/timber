<?php

class TimberMenu extends TimberCore {

    public $MenuItemClass = 'TimberMenuItem';
    public $PostClass = 'TimberPost';

    public $items = null;
    public $id = null;
    public $ID = null;
    public $name = null;
    public $term_id;
    public $title;

    /**
     * @param int $slug
     */
    function __construct($slug = 0) {
        $locations = get_nav_menu_locations();
        if ($slug != 0 && is_numeric($slug)) {
            $menu_id = $slug;
        } else if (is_array($locations) && count($locations)) {
            $menu_id = $this->get_menu_id_from_locations($slug, $locations);
        } else if ($slug === false) {
            $menu_id = false;
        } else {
            $menu_id = $this->get_menu_id_from_terms($slug);
        }
        if ($menu_id) {
            $this->init($menu_id);
        } else {
            $this->init_as_page_menu();
            //TimberHelper::error_log("Sorry, the menu you were looking for wasn't found ('" . $slug . "'). Here's what Timber did find:");
        }
        return null;
    }

    /**
     * @param int $menu_id
     */
    private function init($menu_id) {
        $menu = wp_get_nav_menu_items($menu_id);
        if ($menu) {
            _wp_menu_item_classes_by_context($menu);
            if (is_array($menu)){
                $menu = self::order_children($menu);
            }
            $this->items = $menu;
            $menu_info = wp_get_nav_menu_object($menu_id);
            $this->import($menu_info);
            $this->ID = $this->term_id;
            $this->id = $this->term_id;
            $this->title = $this->name;
        }
    }

    private function init_as_page_menu() {
        $menu = get_pages();
        if ($menu) {
            foreach($menu as $mi) {
                $mi->__title = $mi->post_title;
            }
            _wp_menu_item_classes_by_context($menu);
            if (is_array($menu)){
                $menu = self::order_children($menu);
            }
            $this->items = $menu;
        }
    }

    /**
     * @param string $slug
     * @param array $locations
     * @return integer
     */
    private function get_menu_id_from_locations($slug, $locations) {
        if ($slug === 0) {
            $slug = $this->get_menu_id_from_terms($slug);
        }
        if (is_numeric($slug)) {
            $slug = array_search($slug, $locations);
        }
        if (isset($locations[$slug])) {
            return $locations[$slug];
        }
        return null;
    }

    /**
     * @param int $slug
     * @return int
     */
    private function get_menu_id_from_terms($slug = 0) {
        if (!is_numeric($slug) && is_string($slug)) {
            //we have a string so lets search for that
            $menu_id = get_term_by('slug', $slug, 'nav_menu');
            if ($menu_id) {
                return $menu_id;
            }
            $menu_id = get_term_by('name', $slug, 'nav_menu');
            if ($menu_id) {
                return $menu_id;
            }
        }
        $menus = get_terms('nav_menu', array('hide_empty' => true));
        if (is_array($menus) && count($menus)) {
            if (isset($menus[0]->term_id)) {
                return $menus[0]->term_id;
            }
        }
        return 0;
    }

    /**
     * @param array $menu_items
     * @param int $parent_id
     * @return TimberMenuItem|null
     */
    function find_parent_item_in_menu($menu_items, $parent_id) {
        foreach ($menu_items as &$item) {
            if ($item->ID == $parent_id) {
                return $item;
            }
        }
        return null;
    }

    /**
     * @param array $items
     * @return array
     */
    function order_children($items) {
        $index = array();
        $menu = array();
        foreach ($items as $item) {
            if (isset($item->title)) {
                //items from wp can come with a $title property which conflicts with methods
                $item->__title = $item->title;
                unset($item->title);
            }
            if(isset($item->ID)){
                if (is_object($item) && get_class($item) == 'WP_Post'){
                    $old_menu_item = $item;
                    $item = new $this->PostClass($item);
                }
                $menu_item = new $this->MenuItemClass($item);
                if (isset($old_menu_item)){
                    $menu_item->import_classes($old_menu_item);
                }
                $index[$item->ID] = $menu_item;
            }
        }
        foreach ($index as $item) {
            if (isset($item->menu_item_parent) && $item->menu_item_parent && isset($index[$item->menu_item_parent])) {
                $index[$item->menu_item_parent]->add_child($item);
            } else {
                $menu[] = $item;
            }
        }
        return $menu;
    }

    /**
     * @return array
     */
    function get_items() {
        if (is_array($this->items)) {
            return $this->items;
        }
        return array();
    }
}


