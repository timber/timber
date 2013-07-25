<?php

class TimberMenu extends TimberCore
{

  var $items = null;

  function __construct($slug)
  {
    //$menu = wp_get_nav_menu_object($slug);
    $locations = get_nav_menu_locations();
    if (isset($locations[$slug])) {
      $menu = wp_get_nav_menu_object($locations[$slug]);
      $menu = wp_get_nav_menu_items($menu);
      $menu = self::order_children($menu);
      $this->items = $menu;
    }
    return null;
  }

  function find_parent_item_in_menu($menu_items, $parent_id)
  {
    foreach ($menu_items as &$item) {
      if ($item->ID == $parent_id) {
        return $item;
      }
    }
    return null;
  }

  function order_children($items)
  {
    $menu = array();
    foreach ($items as $item) {
      if ($item->menu_item_parent == 0) {
        $menu[] = new TimberMenuItem($item);
        continue;
      }
      $parent_id = $item->menu_item_parent;
      $parent = self::find_parent_item_in_menu($menu, $parent_id);
      if ($parent) {
        $parent->add_child(new TimberMenuItem($item));
      }
    }
    return $menu;
  }

  function get_items()
  {
    if (is_array($this->items)) {
      return $this->items;
    }
    return array();
  }
}

class TimberMenuItem extends TimberCore
{

  var $children;

  function __construct($data)
  {
    $this->import($data);
  }

  function get_link()
  {
    return $this->get_path();
  }

  function name()
  {
    return $this->post_title;
  }

  function slug()
  {
    return $this->post_name;
  }

  function get_path()
  {
    return $this->url_to_path($this->url);
  }

  function add_child($item)
  {
    if (!isset($this->children)) {
      $this->children = array();
    }
    $this->children[] = $item;
  }

  function get_children()
  {
    if (isset($this->children)) {
      return $this->children;
    }
    return false;
  }
}