<?php

namespace Timber;

use Timber\Core;
use Timber\Post;

/**
 * In Timber, you can use TimberMenu() to make a standard Wordpress menu available to the Twig template as an object you can loop through. And once the menu becomes available to the context, you can get items from it in a way that is a little smoother and more versatile than Wordpress's wp_nav_menu. (You need never again rely on a crazy "Walker Function!"). The first thing to do is to initialize the menu using TimberMenu(). This will make the menu available as an object to work with in the context. (TimberMenu can include a Wordpress menu slug or ID, or it can be sent with no parameter--and guess the right menu.)
 * @example
 * ```php
 * # functions.php
 * add_filter('timber/context', 'add_to_context');
 * function add_to_context($data){
 *		// So here you are adding data to Timber's context object, i.e...
 *  	$data['foo'] = 'I am some other typical value set in your functions.php file, unrelated to the menu';
 *	  	// Now, in similar fashion, you add a Timber menu and send it along to the context.
 * 	  	$data['menu'] = new TimberMenu(); // This is where you can also send a WordPress menu slug or ID
 *	    return $data;
 * }
 *
 * # index.php (or any PHP file)
 * // Since you want a menu object available on every page, I added it to the universal Timber context via the functions.php file. You could also this in each PHP file if you find that too confusing.
 * $context = Timber::get_context();
 * $context['posts'] = Timber::get_posts();
 * Timber::render('index.twig', $context);
 * ?>
 * ```
 *
 * ```twig
 * <nav>
 * 	<ul class="main-nav">
 *		{% for item in menu.get_items %}
 *      	<li class="nav-main-item {{item.classes | join(' ')}}"><a class="nav-main-link" href="{{item.link}}">{{item.title}}</a>
 *         	{% if item.get_children %}
 *           	<ul class="nav-drop">
 *               {% for child in item.get_children %}
 *               	<li class="nav-drop-item"><a href="{{child.link}}">{{child.title}}</a></li>
 *               {% endfor %}
 *              </ul>
 *           {% endif %}
 *           </li>
 *    {% endfor %}
 *    </ul>
 * </nav>
 * ```
 */
class Menu extends Core {

	public $MenuItemClass = 'Timber\MenuItem';
	public $PostClass = 'Timber\Post';

	/**
	 * @api
	 * @var TimberMenuItem[]|null $items you need to iterate through
	 */
	public $items = null;
	/**
	 * @api
	 * @var integer $id the ID# of the menu, corresponding to the wp_terms table
	 */
	public $id;
	public $ID;
	/**
	 * @api
	 * @var string $name of the menu (ex: `Main Navigation`)
	 */
	public $name;
	/**
	 * @var integer $id the ID# of the menu, corresponding to the wp_terms table
	 */
	public $term_id;
	/**
	 * @api
	 * @var string $name of the menu (ex: `Main Navigation`)
	 */
	public $title;

	/**
	 * @param integer|string $slug
	 */
	public function __construct( $slug = 0 ) {
		$menu_id = false;
		$locations = get_nav_menu_locations();
		if ( $slug != 0 && is_numeric($slug) ) {
			$menu_id = $slug;
		} else if ( is_array($locations) && count($locations) ) {
			$menu_id = $this->get_menu_id_from_locations($slug, $locations);
		} else if ( $slug === false ) {
			$menu_id = false;
		}
		if ( !$menu_id ) {
			$menu_id = $this->get_menu_id_from_terms($slug);
		}
		if ( $menu_id ) {
			$this->init($menu_id);
		} else {
			$this->init_as_page_menu();
		}
	}

	/**
	 * @internal
	 * @param int $menu_id
	 */
	protected function init( $menu_id ) {
		$menu = wp_get_nav_menu_items($menu_id);
		if ( $menu ) {
			_wp_menu_item_classes_by_context($menu);
			if ( is_array($menu) ) {
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

	/**
	 * @internal
	 */
	protected function init_as_page_menu() {
		$menu = get_pages(array('sort_column' => 'menu_order'));
		if ( $menu ) {
			foreach ( $menu as $mi ) {
				$mi->__title = $mi->post_title;
			}
			_wp_menu_item_classes_by_context($menu);
			if ( is_array($menu) ) {
				$menu = self::order_children($menu);
			}
			$this->items = $menu;
		}
	}

	/**
	 * @internal
	 * @param string $slug
	 * @param array $locations
	 * @return integer
	 */
	protected function get_menu_id_from_locations( $slug, $locations ) {
		if ( $slug === 0 ) {
			$slug = $this->get_menu_id_from_terms($slug);
		}
		if ( is_numeric($slug) ) {
			$slug = array_search($slug, $locations);
		}
		if ( isset($locations[$slug]) ) {
			$menu_id = $locations[$slug];
			return $menu_id;
		}
	}

	/**
	 * @internal
	 * @param int $slug
	 * @return int
	 */
	protected function get_menu_id_from_terms( $slug = 0 ) {
		if ( !is_numeric($slug) && is_string($slug) ) {
			//we have a string so lets search for that
			$menu = get_term_by('slug', $slug, 'nav_menu');
			if ( $menu ) {
				return $menu->term_id;
			}
			$menu = get_term_by('name', $slug, 'nav_menu');
			if ( $menu ) {
				return $menu->term_id;
			}
		}
		$menus = get_terms('nav_menu', array('hide_empty' => true));
		if ( is_array($menus) && count($menus) ) {
			if ( isset($menus[0]->term_id) ) {
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
	public function find_parent_item_in_menu( $menu_items, $parent_id ) {
		foreach ( $menu_items as &$item ) {
			if ( $item->ID == $parent_id ) {
				return $item;
			}
		}
	}

	/**
	 * @internal
	 * @param array $items
	 * @return array
	 */
	protected function order_children( $items ) {
		$index = array();
		$menu = array();
		foreach ( $items as $item ) {
			if ( isset($item->title) ) {
				//items from wp can come with a $title property which conflicts with methods
				$item->__title = $item->title;
				unset($item->title);
			}
			if ( isset($item->ID) ) {
				if ( is_object($item) && get_class($item) == 'WP_Post' ) {
					$old_menu_item = $item;
					$item = new $this->PostClass($item);
				}
				$menu_item = new $this->MenuItemClass($item);
				if ( isset($old_menu_item) ) {
					$menu_item->import_classes($old_menu_item);
				}
				$index[$item->ID] = $menu_item;
			}
		}
		foreach ( $index as $item ) {
			if ( isset($item->menu_item_parent) && $item->menu_item_parent && isset($index[$item->menu_item_parent]) ) {
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
	public function get_items() {
		if ( is_array($this->items) ) {
			return $this->items;
		}
		return array();
	}
}
