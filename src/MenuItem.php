<?php

namespace Timber;

use stdClass;
use Stringable;
use Timber\Factory\PostFactory;
use Timber\Factory\TermFactory;
use WP_Post;

/**
 * Class MenuItem
 *
 * @api
 */
class MenuItem extends CoreEntity implements Stringable
{
    /**
     * @var string What does this class represent in WordPress terms?
     */
    public $object_type = 'post';

    /**
     * @api
     * @var array Array of children of a menu item. Empty if there are no child menu items.
     */
    public $children = [];

    /**
     * @api
     * @var array Array of class names.
     */
    public $classes = [];

    public $class = '';

    public $level = 0;

    public $post_name;

    public $url;

    public $type;

    /**
     * Protected is needed here since we want to force Twig to use the `title()` method
     * in order to apply the `nav_menu_item_title` filter
     */
    protected $title = '';

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
     * Object ID.
     *
     * @api
     * @since 2.0.0
     * @var int|null Linked object ID.
     */
    public $object_id = null;

    /**
     * Object type.
     *
     * @api
     * @since 2.0.0
     * @var string The underlying menu object type. E.g. a post type name, a taxonomy name or 'custom'.
     */
    public $object;

    protected $_name;

    protected $_menu_item_url;

    /**
     * @internal
     * @param array|object $data The data this MenuItem is wrapping
     * @param Menu $menu The `Menu` object the menu item is associated with.
     * @return MenuItem a new MenuItem instance
     */
    public static function build($data, ?Menu $menu = null): static
    {
        return new static($data, $menu);
    }

    /**
     * @internal
     * @param Menu $menu The `Menu` object the menu item is associated with.
     */
    protected function __construct(
        /**
         * The underlying WordPress Core object.
         *
         * @since 2.0.0
         */
        protected ?WP_Post $wp_object, /**
     * Timber Menu. Previously this was a public property, but converted to a method to avoid
     * recursion (see #2071).
     *
     * @since 1.12.0
     * @see \Timber\MenuItem::menu()
     */
        protected $menu = null
    ) {
        /**
         * @property string $title The nav menu item title.
         */
        $this->title = $this->wp_object->title;

        $this->import($this->wp_object);
        $this->import_classes($this->wp_object);
        $this->id = $this->wp_object->ID;
        $this->ID = $this->wp_object->ID;

        $this->_name = $this->wp_object->name ?? '';
        $this->add_class('menu-item-' . $this->ID);

        /**
         * Because init_as_page_menu already set it to simulate the master object
         *
         * @see Menu::init_as_page_menu
         */
        if (!isset($this->object_id)) {
            $this->object_id = (int) \get_post_meta($this->ID, '_menu_item_object_id', true);
        }
    }

    /**
     * Gets the underlying WordPress Core object.
     *
     * @since 2.0.0
     *
     * @return WP_Post|null
     */
    public function wp_object(): ?WP_Post
    {
        return $this->wp_object;
    }

    /**
     * Add a CSS class the menu item should have.
     *
     * @param string $class_name CSS class name to be added.
     */
    public function add_class(string $class_name)
    {
        // Class name is already there
        if (\in_array($class_name, $this->classes, true)) {
            return;
        }
        $this->classes[] = $class_name;
        $this->update_class();
    }

    /**
     * Add a CSS class the menu item should have.
     *
     * @param string $class_name CSS class name to be added.
     */
    public function remove_class(string $class_name)
    {
        // Class name is already there
        if (!\in_array($class_name, $this->classes, true)) {
            return;
        }
        $class_key = \array_search($class_name, $this->classes, true);
        unset($this->classes[$class_key]);
        $this->update_class();
    }

    /**
     * Update class string
     */
    protected function update_class()
    {
        $this->class = \trim(\implode(' ', $this->classes));
    }

    /**
     * Get the label for the menu item.
     *
     * @api
     * @return string The label for the menu item.
     */
    public function name()
    {
        return $this->title();
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
    public function __toString(): string
    {
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
    public function slug()
    {
        $mo = $this->master_object();
        if ($mo && $mo->post_name) {
            return $mo->post_name;
        }
        return $this->post_name;
    }

    /**
     * Allows dev to access the "master object" (ex: post, page, category, post type object) the menu item represents
     *
     * @api
     * @example
     * ```twig
     * <div>
     *     {% for item in menu.items %}
     *         <a href="{{ item.link }}"><img src="{{ item.master_object.thumbnail }}" /></a>
     *     {% endfor %}
     * </div>
     * ```
     * @return mixed|null Whatever object (Timber\Post, Timber\Term, etc.) the menu item represents.
     */
    public function master_object()
    {
        switch ($this->type) {
            case 'post_type':
                $factory = new PostFactory();
                break;
            case 'taxonomy':
                $factory = new TermFactory();
                break;
            case 'post_type_archive':
                return \get_post_type_object($this->object);
            default:
                $factory = null;
                break;
        }

        return $factory && $this->object_id ? $factory->from($this->object_id) : null;
    }

    /**
     * Add a new `Timber\MenuItem` object as a child of this menu item.
     *
     * @api
     *
     * @param MenuItem $item The menu item to add.
     */
    public function add_child(MenuItem $item)
    {
        $this->children[] = $item;
        $item->level = $this->level + 1;
        if (\count($this->children)) {
            $this->update_child_levels();
        }
    }

    /**
     * Update the level data associated with $this.
     *
     * @internal
     * @return bool|null
     */
    public function update_child_levels()
    {
        if (\is_array($this->children)) {
            foreach ($this->children as $child) {
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
     * @param array|object $data to import.
     */
    public function import_classes($data)
    {
        if (\is_array($data)) {
            $data = (object) $data;
        }
        $this->classes = \array_unique(\array_merge($this->classes, $data->classes ?? []));
        $this->classes = \array_values(\array_filter($this->classes));

        $args = new stdClass();
        if (isset($this->menu->args)) {
            // The args need to be an object.
            $args = $this->menu->args;
        }

        /**
         * @see Walker_Nav_Menu
         */
        $this->classes = \apply_filters(
            'nav_menu_css_class',
            $this->classes,
            $this->wp_object,
            $args,
            0 // TODO: find the right depth
        );

        $this->update_class();
    }

    /**
     * Get children of a menu item.
     *
     * You can also directly access the children through the `$children` property (`item.children`
     * in Twig).
     *
     * @internal
     * @deprecated 2.0.0, use `item.children` instead.
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
    public function get_children()
    {
        Helper::deprecated(
            "{{ item.get_children }}",
            "{{ item.children }}",
            '2.0.0'
        );
        return $this->children();
    }

    /**
     * Checks to see if the menu item is an external link.
     *
     * If your site is `example.org`, then `google.com/whatever` is an external link. This is
     * helpful when you want to style external links differently or create rules for the target of a
     * link.
     *
     * @api
     * @example
     * ```twig
     * <a href="{{ item.link }}" target="{{ item.is_external ? '_blank' : '_self' }}">
     * ```
     *
     * Or when you only want to add a target attribute if it is really needed:
     *
     * ```twig
     * <a href="{{ item.link }}" {{ item.is_external ? 'target="_blank"' }}>
     * ```
     *
     * In combination with `is_target_blank()`:
     *
     * ```twig
     * <a href="{{ item.link }}" {{ item.is_external or item.is_target_blank ? 'target="_blank"' }}>
     * ```
     *
     * @return bool Whether the link is external or not.
     */
    public function is_external()
    {
        if ($this->type !== 'custom') {
            return false;
        }
        return URLHelper::is_external($this->link());
    }

    /**
     * Checks whether the «Open in new tab» option checked in the menu item options.
     *
     * @example
     * ```twig
     * <a href="{{ item.link }}" {{ item.is_target_blank ? 'target="_blank"' }}>
     * ```
     *
     * In combination with `is_external()`
     *
     * ```twig
     * <a href="{{ item.link }}" {{ item.is_target_blank or item.is_external ? 'target="_blank"' }}>
     * ```
     *
     * @return bool Whether the menu item has the «Open in new tab» option checked in the menu item
     *              options.
     */
    public function is_target_blank()
    {
        return '_blank' === $this->meta('_menu_item_target');
    }

    /**
     * Gets the target of a menu item according to the «Open in new tab» option in the menu item
     * options.
     *
     * This function return `_blank` when the option to open a menu item in a new tab is checked in
     * the WordPress backend, and `_self` if the option is not checked. Beware `_self` is the
     * default value for the target attribute, which means you could leave it out. You can use
     * `item.is_target_blank` if you want to use a conditional.
     *
     * @example
     * ```twig
     * <a href="{{ item.link }}" target="{{ item.target }}">
     * ```
     *
     * @return string
     */
    public function target()
    {
        $target = $this->meta('_menu_item_target');
        if (!$target) {
            return '_self';
        }
        return $target;
    }

    /**
     * Timber Menu.
     *
     * @api
     * @since 1.12.0
     * @return Menu The `Menu` object the menu item is associated with.
     */
    public function menu()
    {
        return $this->menu;
    }

    /**
     * Gets a menu item meta value.
     *
     * @api
     * @deprecated 2.0.0, use `{{ item.meta('field_name') }}` instead.
     * @see \Timber\MenuItem::meta()
     *
     * @param string $field_name The field name for which you want to get the value.
     * @return mixed The meta field value.
     */
    public function get_field($field_name = null)
    {
        Helper::deprecated(
            "{{ item.get_field('field_name') }}",
            "{{ item.meta('field_name') }}",
            '2.0.0'
        );
        return $this->meta($field_name);
    }

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
    public function children()
    {
        return $this->children;
    }

    /**
     * Checks to see if the menu item is an external link.
     *
     * @api
     * @deprecated 2.0.0, use `{{ item.is_external }}`
     * @see \Timber\MenuItem::is_external()
     *
     * @return bool Whether the link is external or not.
     */
    public function external()
    {
        Helper::warn('{{ item.external }} is deprecated. Use {{ item.is_external }} instead.');
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
     * @return string A full URL, like `https://mysite.com/thing/`.
     */
    public function link()
    {
        return $this->url;
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
    public function path()
    {
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
    public function title()
    {
        /**
         * @see Walker_Nav_Menu::start_el()
         */
        $title = \apply_filters('nav_menu_item_title', $this->title, $this->wp_object, $this->menu->args ?: new stdClass(), $this->level);
        return $title;
    }

    /**
     * Checks whether the current user can edit the menu item.
     *
     * @api
     * @since 2.0.0
     * @return bool
     */
    public function can_edit(): bool
    {
        return \current_user_can('edit_theme_options');
    }
}
