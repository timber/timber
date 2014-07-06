<?php

class TimberMenuItem extends TimberCore implements TimberCoreInterface {

    public $children;
    public $has_child_class = false;

    public $classes = array();
    public $class;
    public $post_name;
    public $type;

    private $parent_object;

    /**
     *
     *
     * @param array|object $data
     */
    function __construct( $data ) {
        $this->import( $data );
        $this->import_classes( $data );
        if ( isset( $this->name ) ) {
            $this->_name = $this->name;
        }
        $this->name = $this->name();
        $this->add_class( 'menu-item-' . $this->ID );
        $this->parent_object = $data;
    }

    function __toString() {
        return $this->name();
    }

    /**
     *
     *
     * @param string  $class_name
     */
    function add_class( $class_name ) {
        $this->classes[] = $class_name;
        $this->class .= ' ' . $class_name;
    }

    /**
     *
     *
     * @return string
     */
    function name() {
        if ( isset( $this->title ) ) {
            return $this->title;
        }
        if ( isset( $this->_name ) ) {
            return $this->_name;
        }
        return '';
    }

    /**
     *
     *
     * @return string
     */
    function slug() {
        return $this->post_name;
    }

    /**
     *
     *
     * @return string
     */
    function get_link() {
        if (!isset($this->url)){
            if ( isset( $this->_menu_item_type ) && $this->_menu_item_type == 'custom' ) {
                $this->url = $this->_menu_item_url;
            } else if ( isset($this->parent_object) && method_exists( $this->parent_object, 'get_link' ) ) {
                $this->url = untrailingslashit( $this->parent_object->get_link() );
            }
        }
        return untrailingslashit( $this->url );
    }

    /**
     *
     *
     * @return string
     */
    function get_path() {
        return untrailingslashit( TimberURLHelper::get_rel_url( $this->url ) );
    }

    /**
     *
     *
     * @param TimberMenuItem $item
     */
    function add_child( $item ) {
        if ( !$this->has_child_class ) {
            $this->add_class( 'menu-item-has-children' );
            $this->has_child_class = true;
        }
        if ( !isset( $this->children ) ) {
            $this->children = array();
        }
        $this->children[] = $item;
    }

    /**
     *
     *
     * @param object  $data
     */
    function import_classes( $data ) {
        $this->class = trim( implode( ' ', $data->classes ) );
    }

    /**
     *
     *
     * @return array|bool
     */
    function get_children() {
        if ( isset( $this->children ) ) {
            return $this->children;
        }
        return false;
    }

    /**
     *
     *
     * @return bool
     */
    function is_external() {
        if ( $this->type != 'custom' ) {
            return false;
        }
        return TimberURLHelper::is_external( $this->url );
    }

    public function meta( $key ) {
        if ( is_object( $this->parent_object ) && method_exists( $this->parent_object, 'meta' ) ) {
            return $this->meta( $key );
        }
        if ( isset( $this->$key ) ) {
            return $this->$key;
        }
    }

    /* Aliases */

    /**
     *
     *
     * @return array|bool
     */
    public function children() {
        return $this->get_children();
    }

    /**
     *
     *
     * @return bool
     */
    public function external() {
        return $this->is_external();
    }

    /**
     *
     *
     * @return string
     */
    public function link() {
        return $this->get_link();
    }

    /**
     *
     *
     * @return string
     */
    public function path() {
        return untrailingslashit( $this->get_path() );
    }

    /**
     *
     *
     * @return string
     */
    public function permalink() {
        return $this->get_link();
    }

    /**
     *
     *
     * @return string
     */
    public function get_permalink() {
        return $this->get_link();
    }

}
