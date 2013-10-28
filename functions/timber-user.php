<?php

class TimberUser extends TimberCore {

    var $_link;

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

    function get_custom() {
        if ($this->ID) {
            $um = get_user_meta($this->ID);
            $custom = new stdClass();
            foreach ($um as $key => $value) {
                $v = $value[0];
                $custom->$key = $v;
                if (is_serialized($v)) {
                    if (gettype(unserialize($v)) == 'array') {
                        $custom->$key = unserialize($v);
                    }
                }
            }
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