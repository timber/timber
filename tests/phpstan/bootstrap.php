<?php

define('WP_CONTENT_DIR', '');
define('WP_CONTENT_URL', '');
define('TIMBER_LOC', '');

function wpml_object_id_filter($locations, $nav_menu)
{
    return 'string';
}
function get_coauthors($id)
{
    return [];
}

class WPML_LS_Menu_Item
{
    /** @var string|int|null */
    public $ID;

    /** @var string|null */
    public $attr_title;

    /** @var string[] */
    public $classes = [];

    /** @var int|null */
    public $db_id;

    /** @var string|null */
    public $description;

    /** @var int|null */
    public $menu_item_parent;

    /** @var string */
    public $object = 'wpml_ls_menu_item';

    /** @var int|null */
    public $object_id;

    /** @var int|null */
    public $post_parent;

    /** @var string|null */
    public $post_title;

    /** @var string|null */
    public $target;

    /** @var string|null */
    public $title;

    /** @var string */
    public $type = 'wpml_ls_menu_item';

    /** @var string|null */
    public $type_label;

    /** @var string|null */
    public $url;

    /** @var string|null */
    public $xfn;

    /** @var bool */
    public $_invalid = false;

    /** @var int|null */
    public $menu_order;

    /** @var string */
    public $post_type = 'nav_menu_item';

    /**
     * @param array  $language
     * @param string $item_content
     */
    public function __construct($language, $item_content)
    {
    }

    /**
     * @param string $property
     * @return mixed
     */
    public function __get($property)
    {
    }
}

// Loads twig_array_filter()
new Twig\Extension\CoreExtension();
