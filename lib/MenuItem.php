<?php

namespace Timber;

use Timber\Core;
use Timber\CoreInterface;

use Timber\URLHelper;

class MenuItem extends Core implements CoreInterface {

	public $children;
	public $has_child_class = false;
	public $classes = array();
	public $class = '';
	public $level = 0;
	public $post_name;
	public $type;
	public $url;

	public $PostClass = 'TimberPost';

	protected $_name;
	protected $_menu_item_object_id;
	protected $_menu_item_url;
	protected $menu_object;
	protected $master_object;

	/**
	 *
	 *
	 * @param array|object $data
	 */
	public function __construct( $data ) {
		$data = (object) $data;
		$this->import($data);
		$this->import_classes($data);
		if ( isset($this->name) ) {
			$this->_name = $this->name;
		}
		$this->name = $this->name();
		$this->add_class('menu-item-'.$this->ID);
		$this->menu_object = $data;
	}

	/**
	 * @return string the label for the menu item
	 */
	public function __toString() {
		return $this->name();
	}

	/**
	 * add a class the menu item should have
	 * @param string  $class_name to be added
	 */
	public function add_class( $class_name ) {
		$this->classes[] = $class_name;
		$this->class .= ' '.$class_name;
	}

	/**
	 * The label for the menu item
	 * @api
	 * @return string
	 */
	public function name() {
		if ( $title = $this->title() ) {
			return $title;
		}
		if ( isset($this->_name) ) {
			return $this->_name;
		}
		return '';
	}

	/**
	 * The slug for the menu item
	 * @api
	 * @example
	 * ```twig
	 * <ul>
	 *     {% for item in menu.items %}
	 *         <li class="{{item.slug}}">
	 *             <a href="{{item.link}}">{{item.name}}</a>
	 *          </li>
	 *     {% endfor %}
	 * </ul>
	 * @return string the slug of the menu item kinda-like-this
	 */
	public function slug() {
		if ( !isset($this->master_object) ) {
			$this->master_object = $this->get_master_object();
		}
		if ( isset($this->master_object->post_name) && $this->master_object->post_name ) {
			return $this->master_object->post_name;
		}
		return $this->post_name;
	}

	/**
	 * @internal
	 * @return mixed whatever object (Post, Term, etc.) the menu item represents
	 */
	protected function get_master_object() {
		if ( isset($this->_menu_item_object_id) ) {
			return new $this->PostClass($this->_menu_item_object_id);
		}
	}

	/**
	 * @internal
	 * @see TimberMenuItem::link
	 * @deprecated 1.0
	 * @return string an absolute URL http://example.org/my-page
	 */
	function get_link() {
		return $this->link();
	}

	/**
	 * @internal
	 * @see TimberMenuItem::path()
	 * @deprecated 1.0
	 * @return string a relative url /my-page
	 */
	function get_path() {
		return $this->path();
	}

	/**
	 *
	 *
	 * @param TimberMenuItem $item
	 */
	function add_child( $item ) {
		if ( !$this->has_child_class ) {
			$this->add_class('menu-item-has-children');
			$this->has_child_class = true;
		}
		if ( !isset($this->children) ) {
			$this->children = array();
		}
		$this->children[] = $item;
		$item->level = $this->level + 1;
		if ( $item->children ) {
			$this->update_child_levels();
		}
	}

	/**
	 *
	 * @internal
	 * @return bool 
	 */
	function update_child_levels() {
		if ( is_array($this->children) ) {
			foreach ( $this->children as $child ) {
				$child->level = $this->level + 1;
				$child->update_child_levels();
			}
			return true;
		}
	}

	/**
	 * Imports the classes to be used in CSS
	 * @internal
	 * @param array|object  $data
	 */
	function import_classes( $data ) {
		if ( is_array($data) ) {
			$data = (object) $data;
		}
		$this->classes = array_merge($this->classes, $data->classes);
		$this->classes = array_unique($this->classes);
		$this->classes = apply_filters('nav_menu_css_class', $this->classes, $this);
		$this->class = trim(implode(' ', $this->classes));
	}

	/**
	 *
	 * @internal
	 * @return array|bool
	 */
	function get_children() {
		if ( isset($this->children) ) {
			return $this->children;
		}
		return false;
	}

	/**
	 * Checks to see if the menu item is an external link so if my site is `example.org`, `google.com/whatever` is an external link. Helpful when creating rules for the target of a link
	 * @api
	 * @example
	 * ```twig
	 * <a href="{{ item.link }}" target="{{ item.is_external ? '_blank' : '_self' }}">
	 * ```
	 * @return bool
	 */
	function is_external() {
		if ( $this->type != 'custom' ) {
			return false;
		}
		return URLHelper::is_external($this->url);
	}

	/**
	 * @param string $key lookup key
	 * @return mixed whatever value is storied in the database
	 */
	public function meta( $key ) {
		if ( is_object($this->menu_object) && method_exists($this->menu_object, 'meta') ) {
			return $this->menu_object->meta($key);
		}
		if ( isset($this->$key) ) {
			return $this->$key;
		}
	}

	/* Aliases */

	/**
	 * Get the child [TimberMenuItems](#TimberMenuItem)s of a [TimberMenuItem](#TimberMenuItem)
	 * @api
	 * @return array|bool
	 */
	public function children() {
		return $this->get_children();
	}

	/**
	 * Checks to see if a link is external, helpful when creating rules for the target of a link
	 * @see TimberMenuItem::is_external
	 * @return bool
	 */
	public function external() {
		return $this->is_external();
	}

	/**
	 * Get the full link to a Menu Item
	 * @api
	 * @example
	 * ```twig
	 * {% for item in menu.items %}
	 *     <li><a href="{{ item.link }}">{{ item.title }}</a></li>
	 * {% endfor %}
	 * ```
	 * @return string a full URL like http://mysite.com/thing/
	 */
	public function link() {
		if ( !isset($this->url) || !$this->url ) {
			if ( isset($this->_menu_item_type) && $this->_menu_item_type == 'custom' ) {
				$this->url = $this->_menu_item_url;
			} else if ( isset($this->menu_object) && method_exists($this->menu_object, 'get_link') ) {
					$this->url = $this->menu_object->get_link();
				}
		}
		return $this->url;
	}

	/**
	 * Gets the link a menu item points at
	 * @internal
	 * @deprecated since 0.21.7 use link instead
	 * @see link()
	 * @return string a full URL like http://mysite.com/thing/
	 */
	public function permalink() {
		Helper::warn('{{item.permalink}} is deprecated, use {{item.link}} instead');
		return $this->link();
	}

	/**
	 * Return the relative path of a Menu Item's link
	 * @example
	 * ```twig
	 * {% for item in menu.items %}
	 *     <li><a href="{{ item.path }}">{{ item.title }}</a></li>
	 * {% endfor %}
	 * ```
	 * @see get_path()
	 * @return string the path of a URL like /foo
	 */
	public function path() {
		return URLHelper::get_rel_url($this->link());
	}

	/**
	 * Gets the public label for the menu item
	 * @example
	 * ```twig
	 * {% for item in menu.items %}
	 *     <li><a href="{{ item.link }}">{{ item.title }}</a></li>
	 * {% endfor %}
	 * ```
	 * @return string the public label like Foo
	 */
	public function title() {
		if ( isset($this->__title) ) {
			return $this->__title;
		}
	}
}