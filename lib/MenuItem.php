<?php

namespace Timber;

use Timber\Core;
use Timber\CoreInterface;

use Timber\URLHelper;

class MenuItem extends Core implements CoreInterface {
	/**
	 * @api
	 * @var array Array of children of a menu item. Empty if there are no child menu items.
	 */
	public $children = array();

	/**
	 * @api
	 * @var bool Whether the menu item has a `menu-item-has-children` CSS class.
	 */
	public $has_child_class = false;

	/**
	 * @api
	 * @var array Array of class names.
	 */
	public $classes = array();
	public $class = '';
	public $level = 0;
	public $post_name;
	public $url;

	public $PostClass = 'Timber\Post';

	/**
	 * Inherited property. Listed here to make it available in the documentation.
	 *
	 * @api
	 * @see _wp_menu_item_classes_by_context()
	 * @var bool Whether the menu item links to the currently displayed page.
	 */
	public $current;

	/**
	 * Inherited property. Listed here to make it available in the documentation.
	 *
	 * @api
	 * @see _wp_menu_item_classes_by_context()
	 * @var bool Whether the menu item refers to the parent item of the currently displayed page.
	 */
	public $current_item_parent;

	/**
	 * Inherited property. Listed here to make it available in the documentation.
	 *
	 * @api
	 * @see _wp_menu_item_classes_by_context()
	 * @var bool Whether the menu item refers to an ancestor (including direct parent) of the
	 *      currently displayed page.
	 */
	public $current_item_ancestor;

	/**
	 * Timber Menu. Previously this was a public property, but converted to a method to avoid
	 * recursion (see #2071).
	 *
	 * @since 1.12.0
	 * @see \Timber\Menu::menu();
	 * @var \Timber\Menu The `Timber\Menu` object the menu item is associated with.
	 */
	protected $menu;

	protected $_name;
	protected $_menu_item_object_id;
	protected $_menu_item_url;
	protected $menu_object;

	/**
	 * @internal
	 * @param array|object $data
	 * @param \Timber\Menu $menu The `Timber\Menu` object the menu item is associated with.
	 */
	public function __construct( $data, $menu = null ) {
		$this->menu = $menu;
		$data       = (object) $data;

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
	 * Add a CSS class the menu item should have.
	 *
	 * @param string $class_name CSS class name to be added.
	 */
	public function add_class( $class_name ) {
		$this->classes[] = $class_name;
		$this->class .= ' '.$class_name;
	}

	/**
	 * Get the label for the menu item.
	 *
	 * @api
	 * @return string The label for the menu item.
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
	 * Magic method to get the label for the menu item.
	 *
	 * @api
	 * @example
	 * ```twig
	 * <a href="{{ item.link }}">{{ item }}</a>
	 * ```
	 * @see \Timber\MenuItem::name()
	 * @return string The label for the menu item.
	 */
	public function __toString() {
		return $this->name();
	}

	/**
	 * Get the slug for the menu item.
	 *
	 * @api
	 * @example
	 * ```twig
	 * <ul>
	 *     {% for item in menu.items %}
	 *         <li class="{{ item.slug }}">
	 *             <a href="{{ item.link }}">{{ item.name }}</a>
	 *          </li>
	 *     {% endfor %}
	 * </ul>
	 * ```
	 * @return string The URL-safe slug of the menu item.
	 */
	public function slug() {
		$mo = $this->master_object();
		if ( $mo && $mo->post_name ) {
			return $mo->post_name;
		}
		return $this->post_name;
	}

	/**
	 * Allows dev to access the "master object" (ex: post or page) the menu item represents
	 * @api
	 * @example
	 * ```twig
	 * <div>
	 *     {% for item in menu.items %}
	 *         <a href="{{ item.link }}"><img src="{{ item.master_object.thumbnail }}" /></a>
	 *     {% endfor %}
	 * </div>
	 * ```
	 * @return mixed Whatever object (Timber\Post, Timber\Term, etc.) the menu item represents.
	 */
	public function master_object() {
		if ( isset($this->_menu_item_object_id) ) {
			return new $this->PostClass($this->_menu_item_object_id);
		}
		if ( isset($this->menu_object) ) {
			return new $this->PostClass($this->menu_object);
		}
	}

	/**
	 * Get link.
	 *
	 * @internal
	 * @see \Timber\MenuItem::link()
	 * @deprecated since 1.0
	 * @codeCoverageIgnore
	 * @return string An absolute URL, e.g. `http://example.org/my-page`.
	 */
	public function get_link() {
		return $this->link();
	}

	/**
	 * Get path.
	 *
	 * @internal
	 * @codeCoverageIgnore
	 * @see        \Timber\MenuItem::path()
	 * @deprecated since 1.0
	 * @return string A relative URL, e.g. `/my-page`.
	 */
	public function get_path() {
		return $this->path();
	}

	/**
	 * Add a new `Timber\MenuItem` object as a child of this menu item.
	 *
	 * @param \Timber\MenuItem $item The menu item to add.
	 */
	public function add_child( $item ) {
		if ( !$this->has_child_class ) {
			$this->add_class('menu-item-has-children');
			$this->has_child_class = true;
		}
		$this->children[] = $item;
		$item->level = $this->level + 1;
		if ( count($this->children) ) {
			$this->update_child_levels();
		}
	}

	/**
	 * @internal
	 * @return bool|null
	 */
	public function update_child_levels() {
		if ( is_array($this->children) ) {
			foreach ( $this->children as $child ) {
				$child->level = $this->level + 1;
				$child->update_child_levels();
			}
			return true;
		}
	}

	/**
	 * Imports the classes to be used in CSS.
	 *
	 * @internal
	 *
	 * @param array|object $data
	 */
	public function import_classes( $data ) {
		if ( is_array($data) ) {
			$data = (object) $data;
		}
		$this->classes = array_merge($this->classes, $data->classes);
		$this->classes = array_unique($this->classes);

		$options = new \stdClass();
		if ( isset($this->menu()->options) ) {
			// The options need to be an object.
			$options = (object) $this->menu()->options;
		}

		/**
		 * Filters the CSS classes applied to a menu item’s list item.
		 *
		 * @param string[]         $classes An array of the CSS classes that can be applied to the
		 *                                  menu item’s `<li>` element.
		 * @param \Timber\MenuItem $item    The current menu item.
		 * @param \stdClass $args           An object of wp_nav_menu() arguments. In Timber, we
		 *                                  don’t have these arguments because we don’t use a menu
		 *                                  walker. Instead, you get the options that were used to
		 *                                  create the `Timber\Menu` object.
		 * @param int              $depth   Depth of menu item.
		 */
		$this->classes = apply_filters(
			'nav_menu_css_class',
			$this->classes,
			$this,
			$options,
			0
		);

		$this->class = trim(implode(' ', $this->classes));
	}

	/**
	 * Get children of a menu item.
	 *
	 * You can also directly access the children through the `$children` property (`item.children`
	 * in Twig).
	 *
	 * @internal
	 * @example
	 * ```twig
	 * {% for child in item.get_children %}
	 *     <li class="nav-drop-item">
	 *         <a href="{{ child.link }}">{{ child.title }}</a>
	 *     </li>
	 * {% endfor %}
	 * ```
	 * @return array|bool Array of children of a menu item. Empty if there are no child menu items.
	 */
	public function get_children() {
		if ( isset($this->children) ) {
			return $this->children;
		}
		return false;
	}

	/**
	 * Checks to see if the menu item is an external link.
	 *
	 * If your site is `example.org`, then `google.com/whatever` is an external link. This is
	 * helpful when you want to create rules for the target of a link.
	 *
	 * @api
	 * @example
	 * ```twig
	 * <a href="{{ item.link }}" target="{{ item.is_external ? '_blank' : '_self' }}">
	 * ```
	 * @return bool Whether the link is external or not.
	 */
	public function is_external() {
		if ( $this->type() != 'custom' ) {
			return false;
		}
		return URLHelper::is_external($this->url);
	}

	/**
	 * Get the type of the menu item.
	 *
	 * Depending on what is the menu item links to. Can be `post_type` for posts, pages and custom
	 * posts, `post_type_archive` for post type archive pages, `taxonomy` for terms or `custom` for
	 * custom links.
	 *
	 * @api
	 * @since 1.0.4
	 * @return string The type of the menu item.
	 */
	public function type() {
		return $this->_menu_item_type;
	}

	/**
	 * Timber Menu.
	 *
	 * @api
	 * @since 1.12.0
	 * @return \Timber\Menu The `Timber\Menu` object the menu item is associated with.
	 */
	public function menu() {
		return $this->menu;
	}


	/**
	 * Get a meta value of the menu item.
	 *
	 * Plugins like Advanced Custom Fields allow you to set custom fields for menu items. With this
	 * method you can retrieve the value of these.
	 *
	 * @example
	 * ```twig
	 * <a class="icon-{{ item.meta('icon') }}" href="{{ item.link }}">{{ item.title }}</a>
	 * ```
	 * @api
	 * @param string $key The meta key to get the value for.
	 * @return mixed Whatever value is stored in the database.
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
	 * Get the child menu items of a `Timber\MenuItem`.
	 *
	 * @api
	 * @example
	 * ```twig
	 * {% for child in item.children %}
	 *     <li class="nav-drop-item">
	 *         <a href="{{ child.link }}">{{ child.title }}</a>
	 *     </li>
	 * {% endfor %}
	 * ```
	 * @return array|bool Array of children of a menu item. Empty if there are no child menu items.
	 */
	public function children() {
		return $this->get_children();
	}

	/**
	 * Check if a link is external.
	 *
	 * This is helpful when creating rules for the target of a link.
	 *
	 * @internal
	 * @see \Timber\MenuItem::is_external()
	 * @return bool Whether the link is external or not.
	 */
	public function external() {
		return $this->is_external();
	}

	/**
	 * Get the full link to a menu item.
	 *
	 * @api
	 * @example
	 * ```twig
	 * {% for item in menu.items %}
	 *     <li><a href="{{ item.link }}">{{ item.title }}</a></li>
	 * {% endfor %}
	 * ```
	 * @return string A full URL, like `http://mysite.com/thing/`.
	 */
	public function link() {
		if ( !isset($this->url) || !$this->url ) {
			if ( isset($this->_menu_item_type) && $this->_menu_item_type == 'custom' ) {
				$this->url = $this->_menu_item_url;
			} else if ( isset($this->menu_object) && method_exists($this->menu_object, 'get_link') ) {
					$this->url = $this->menu_object->link();
				}
		}
		return $this->url;
	}

	/**
	 * Get the link the menu item points at.
	 *
	 * @internal
	 * @deprecated since 0.21.7 Use link method instead.
	 * @see \Timber\MenuItem::link()
	 * @codeCoverageIgnore
	 * @return string A full URL, like `http://mysite.com/thing/`.
	 */
	public function permalink() {
		Helper::warn('{{ item.permalink }} is deprecated, use {{ item.link }} instead');
		return $this->link();
	}

	/**
	 * Get the relative path of the menu item’s link.
	 *
	 * @api
	 * @example
	 * ```twig
	 * {% for item in menu.items %}
	 *     <li><a href="{{ item.path }}">{{ item.title }}</a></li>
	 * {% endfor %}
	 * ```
	 * @return string The path of a URL, like `/foo`.
	 */
	public function path() {
		return URLHelper::get_rel_url($this->link());
	}

	/**
	 * Get the public label for the menu item.
	 *
	 * @api
	 * @example
	 * ```twig
	 * {% for item in menu.items %}
	 *     <li><a href="{{ item.link }}">{{ item.title }}</a></li>
	 * {% endfor %}
	 * ```
	 * @return string The public label, like "Foo".
	 */
	public function title() {
		if ( isset($this->__title) ) {
			return $this->__title;
		}
	}

	/**
	 * Get the featured image of the post associated with the menu item.
	 *
	 * @api
	 * @deprecated since 1.5.2 to be removed in v2.0
	 * @example
	 * ```twig
	 * {% for item in menu.items %}
	 *     <li><a href="{{ item.link }}"><img src="{{ item.thumbnail }}"/></a></li>
	 * {% endfor %}
	 * ```
	 * @return \Timber\Image|null The featured image object.
	 */
	public function thumbnail() {
		$mo = $this->master_object();
		if ( $mo && method_exists($mo, 'thumbnail') ) {
			return $mo->thumbnail();
		}
	}
}
