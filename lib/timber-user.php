<?php

class TimberUser extends TimberCore implements TimberCoreInterface
{
    public $object_type = 'user';
    public static $representation = 'user';

    public $_link;

    public $description;
    public $display_name;
    public $id;
    public $name;
    public $user_nicename;

    /**
     * @param int|bool $uid
     */
    public function __construct($uid = false)
    {
        $this->init($uid);
    }

    /**
     * @return string
     */
    public function __toString()
    {
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
    public function get_meta($field_name)
    {
        return $this->get_meta_field($field_name);
    }

    /**
     * @param string $field
     * @param mixed $value
     */
    public function __set($field, $value)
    {
        if ($field == 'name') {
            $this->display_name = $value;
        }
        $this->$field = $value;
    }

    /**
     * @return string
     */
    public function get_link()
    {
        if (!$this->_link) {
            $this->_link = untrailingslashit(get_author_posts_url($this->ID));
        }
        return $this->_link;
    }

    /**
     * @param int|bool $uid
     */
    public function init($uid = false)
    {
        if ($uid === false) {
            $uid = get_current_user_id();
        }
        if (is_object($uid) || is_array($uid)) {
            $data = $uid;
            if (is_array($uid)) {
                $data =  (object) $uid;
            }
            $uid = $data->ID;
        }
        if (is_numeric($uid)) {
            $data = get_userdata($uid);
        }
        if (isset($data) && is_object($data)) {
            if (isset($data->data)) {
                $this->import($data->data);
            } else {
                $this->import($data);
            }
        }
        $this->id = $this->ID;
        $this->name = $this->name();
        $this->import_custom();
    }

    /**
     * @param string $field_name
     * @return mixed
     */
    public function get_meta_field($field_name)
    {
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
    public function get_custom()
    {
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

    public function import_custom()
    {
        $custom = $this->get_custom();
        $this->import($custom);
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->display_name;
    }

    /**
     * @return string
     */
    public function get_permalink()
    {
        return $this->get_link();
    }

    /**
     * @return string
     */
    public function permalink()
    {
        return $this->get_permalink();
    }

    /**
     * @return string ex: /author/lincoln
     */
    public function get_path()
    {
        return TimberURLHelper::get_rel_url($this->get_link());
    }

    /**
     * @param string $field_name
     * @return mixed
     */
    public function meta($field_name)
    {
        return $this->get_meta_field($field_name);
    }

    /**
     * @return string
     */
    public function path()
    {
        return $this->get_path();
    }

    /**
     * @return string
     */
    public function slug()
    {
        return $this->user_nicename;
    }

    /**
     * @return string
     */
    public function link()
    {
        return $this->get_link();
    }
}
