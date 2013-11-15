<?php

class TimberUser extends TimberCore {

    var $_link;
    var $object_type = 'user';

    public static $representation = 'user';

    function __construct($uid = false) {
        $this->init($uid);
    }

    function __toString(){
        $name = $this->name();
        if (strlen($name)){
            return $name;
        }
        if (strlen($this->name)){
            return $this->name;
        }
        return '';
    }

    function get_meta($field_name){
        $value = null;
        $value = apply_filters('timber_user_get_meta_field_pre', $value, $this->ID, $field_name, $this);
        if ($value === null){
            $value = get_post_meta($this->ID, $field_name, true);
        }
        $value = apply_filters('timber_user_get_meta_field', $value, $this->ID, $field_name, $this);
        return $value;
    }

    function __set($field, $value){
        if ($field == 'name'){
            $this->display_name = $value;
        }
        $this->$field = $value;
    }

    public function get_link() {
        if (!$this->_link){
            $this->_link = get_author_posts_url($this->ID);
        }
        return $this->_link;
    }

    function init($uid = false) {
        if ($uid === false) {
            $uid = get_current_user_id();
        }
        if ($uid){
            $data = get_userdata($uid);
            if (is_object($data) && isset($data)) {
                $this->import($data->data);
            }
            $this->ID = $uid;
            $this->import_custom();
        }
    }

    function get_meta_field($field_name){
        $value = null;
        $value = apply_filters('timber_user_get_meta_field_pre', $value, $this->ID, $field_name, $this);
        if ($value === null){
            $value = get_user_meta($this->ID, $field_name, true);
        }
        $value = apply_filters('timber_user_get_meta_field', $value, $this->ID, $field_name, $this);
        return $value;
    }

    function get_custom() {
        if ($this->ID) {
            $um = array();
            $um = apply_filters('timber_user_get_meta_pre', $um, $this->ID, $this);
            if (empty($um)){
                $um = get_user_meta($this->ID);
            }
            $custom = array();
            foreach ($um as $key => $value) {
                if (is_array($value) && count($value) == 1){
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

    function name() {
        return $this->display_name;
    }

    function get_permalink(){
        return $this->get_link();
    }

    function permalink() {
        return $this->get_link();
    }

    function get_path() {
        return $this->get_link();
    }

    function meta($field_name){
        return $this->get_meta_field($field_name);
    }

    function path() {
        return $this->get_path();
    }

    function slug() {
        return $this->user_nicename;
    }

    function link(){
        return $this->get_link();
    }

}