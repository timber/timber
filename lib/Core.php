<?php

namespace Timber;

abstract class Core {

	public $id;
	public $ID;
	public $object_type;

	/**
	 *
	 * @return boolean
	 */
	public function __isset( $field ) {
		if ( isset($this->$field) ) {
			return $this->$field;
		}
		return false;
	}

	/**
	 * This is helpful for twig to return properties and methods see: https://github.com/fabpot/Twig/issues/2
	 * @return mixed
	 */
	public function __call( $field, $args ) {
		return $this->__get($field);
	}

	/**
	 * This is helpful for twig to return properties and methods see: https://github.com/fabpot/Twig/issues/2
	 *
	 * @return mixed
	 */
	public function __get( $field ) {
		if ( property_exists($this, $field) ) {
			return $this->$field;
		}
		if ( method_exists($this, 'meta') && $meta_value = $this->meta($field) ) {
			return $this->$field = $meta_value;
		}
		if ( method_exists($this, $field) ) {
			return $this->$field = $this->$field();
		}
		return $this->$field = false;
	}

	/**
	 * Takes an array or object and adds the properties to the parent object
	 * @example
	 * ```php
	 * $data = array('airplane' => '757-200', 'flight' => '5316');
	 * $post = new TimberPost()
	 * $post->import(data);
	 * echo $post->airplane; //757-200
	 * ```
	 * @param array|object $info an object or array you want to grab data from to attach to the Timber object
	 */
	public function import( $info, $force = false ) {
		if ( is_object($info) ) {
			$info = get_object_vars($info);
		}
		if ( is_array($info) ) {
			foreach ( $info as $key => $value ) {
				if ( $key === '' || ord($key[0]) === 0 ) {
					continue;
				}
				if ( !empty($key) && $force ) {
					$this->$key = $value;
				} else if ( !empty($key) && !method_exists($this, $key) ) {
					$this->$key = $value;
				}
			}
		}
	}


	/**
	 * @ignore
	 * @param string  $key
	 * @param mixed   $value
	 */
	public function update( $key, $value ) {
		update_metadata($this->object_type, $this->ID, $key, $value);
	}

	/**
	 * Can you edit this post/term/user? Well good for you. You're no better than me.
	 * @example
	 * ```twig
	 * {% if post.can_edit %}
	 * <a href="{{ post.edit_link }}">Edit</a>
	 * {% endif %}
	 * ```
	 * ```html
	 * <a href="http://example.org/wp-admin/edit.php?p=242">Edit</a>
	 * ```
	 * @return bool
	 */
	public function can_edit() {
		if ( !function_exists('current_user_can') ) {
			return false;
		}
		if ( current_user_can('edit_post', $this->ID) ) {
			return true;
		}
		return false;
	}

	/**
	 *
	 *
	 * @return array
	 */
	public function get_method_values() {
		$ret = array();
		$ret['can_edit'] = $this->can_edit();
		return $ret;
	}

	/**
	 * @param string $field_name
	 * @return mixed
	 */
	public function get_field( $field_name ) {
		return $this->get_meta_field($field_name);
	}
}
