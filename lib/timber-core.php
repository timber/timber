<?php

abstract class TimberCore
{
    public $id;
    public $ID;
    public $object_type;

    /**
     *
     *
     * @return boolean
     */
    public function __isset($field)
    {
        if (isset($this->$field)) {
            return $this->$field;
        }
        return false;
    }

    /**
     * This is helpful for twig to return properties and methods see: https://github.com/fabpot/Twig/issues/2
     * @return mixed
     */
    public function __call($field, $args)
    {
        return $this->__get($field);
    }

    /**
     * This is helpful for twig to return properties and methods see: https://github.com/fabpot/Twig/issues/2
     *
     * @return mixed
     */
    public function __get($field)
    {
        if (isset($this->$field)) {
            return $this->$field;
        }
        if ($meta_value = $this->meta($field)) {
            return $this->$field = $meta_value;
        }
        if (method_exists($this, $field)) {
            return $this->$field = $this->$field();
        }
        return $this->$field = false;
    }

    /**
     *
     *
     * @param array|object $info an object or array you want to grab data from to attach to the Timber object
     */
    public function import($info, $force = false)
    {
        if (is_object($info)) {
            $info = get_object_vars($info);
        }
        if (is_array($info)) {
            foreach ($info as $key => $value) {
                if (!empty($key) && $force) {
                    $this->$key = $value;
                } elseif (!empty($key) && !method_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }


    /**
     *
     *
     * @param string  $key
     * @param mixed   $value
     */
    public function update($key, $value)
    {
        update_metadata($this->object_type, $this->ID, $key, $value);
    }

    /**
     * @return bool
     */
    public function can_edit()
    {
        if (!function_exists('current_user_can')) {
            return false;
        }
        if (current_user_can('edit_post', $this->ID)) {
            return true;
        }
        return false;
    }

    /**
     *
     *
     * @return array
     */
    public function get_method_values()
    {
        $ret = array();
        $ret['can_edit'] = $this->can_edit();
        return $ret;
    }
}
