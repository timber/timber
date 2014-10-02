<?php

class TimberUser extends TimberCore implements TimberCoreInterface {

    public $object_type = 'user';
    public static $representation = 'user';

    public $_link;

    public $display_name;
    public $id;
    public $name;
    public $user_nicename;

    /**
     * @param int|bool $uid
     */
    function __construct($uid = false) {
        $this->init($uid);
    }

    /**
     * @return string
     */
    function __toString() {
        $name = $this->name();
        if (strlen($name)) {
            return $name;
        }
        if (strlen($this->name)) {
            return $this->name;
        }
        return '';
    }

    /**
     * @param string $field_name
     * @return null
     */
    function get_meta($field_name) {
        return $this->get_meta_field( $field_name );
    }

    /**
     * @param string $field
     * @param mixed $value
     */
    function __set($field, $value) {
        if ($field == 'name') {
            $this->display_name = $value;
        }
        $this->$field = $value;
    }

    /**
     * @return string
     */
    public function get_link() {
        if (!$this->_link) {
            $this->_link = get_author_posts_url($this->ID);
        }
        return $this->_link;
    }

    /**
     * @param int|bool $uid
     */
    function init($uid = false) {
        $uid = ($uid === false) ? get_current_user_id : $uid;
        if (is_object($uid) || is_array($uid)){
            $data = $uid;
            if (is_array($uid)){
                $data =  (object) $uid;
            }
            $uid = $data->ID;
        }
        $data = (is_numeric($uid)) ? get_userdata($uid) : $data;
        if (isset($data) && is_object($data)) {
            $this->import((isset($data->data)) ? $data->data : $data);
        }
        $this->id = $this->ID;
        $this->name = $this->name();
        $this->import_custom();
    }

    /**
     * @param string $field_name
     * @return mixed
     */
    function get_meta_field($field_name) {
        $value = null;
        $value = apply_filters('timber_user_get_meta_field_pre', $value, $this->ID, $field_name, $this);
        if ($value === null) {
            $value = get_user_meta($this->ID, $field_name, true);
        }
        $value = apply_filters('timber_user_get_meta_field', $value, $this->ID, $field_name, $this);
        return $value;
    }

    /**
     * @return array|null
     */
    function get_custom() {
        if ($this->ID) {
            $um = array();
            $um = apply_filters('timber_user_get_meta_pre', $um, $this->ID, $this);
            if (empty($um)) {
                $um = get_user_meta($this->ID);
            }
            $custom = array();
            foreach ($um as $key => $value) {
                if (is_array($value) && count($value) == 1) {
                    $value = $value[0];
                }
                $custom[$key] = maybe_unserialize($value);
            }
            $custom = apply_filters('timber_user_get_meta', $custom, $this->ID, $this);
            return $custom;
        }
        return null;
    }

    function import_custom() {
        $custom = $this->get_custom();
        $this->import($custom);
    }

    /**
     * @return string
     */
    function name() {
        return $this->display_name;
    }

    /**
     * @return string
     */
    function get_permalink() {
        return $this->get_link();
    }

    /**
     * @return string
     */
    function permalink() {
        return $this->get_link();
    }

    /**
     * @return string
     */
    function get_path() {
        return $this->get_link();
    }

    /**
     * @param string $field_name
     * @return mixed
     */
    function meta($field_name) {
        return $this->get_meta_field($field_name);
    }

    /**
     * @return string
     */
    function path() {
        return $this->get_path();
    }

    /**
     * @return string
     */
    function slug() {
        return $this->user_nicename;
    }

    /**
     * @return string
     */
    function link() {
        return $this->get_link();
    }

}
