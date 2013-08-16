<?php

class TimberUser extends TimberCore {

    function __construct($uid = false) {
        $this->init($uid);
    }

    public function get_link() {
        $p = WPHelper::get_path_base();
        return $p . 'author/' . $this->slug();
    }

    function init($uid = false) {
        if (!$uid) {
            $uid = get_current_user_id();
        }
        if (function_exists('get_userdata')) {
            $data = get_userdata($uid);
            if (is_object($data) && isset($data)) {
                $this->import($data->data);
            }
        }

        $this->ID = $uid;
        $this->import_custom();
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
}