<?php

namespace Timber;

/**
 * Class Core
 */
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
	 * "Magic" method dispatcher for meta fields, for convience in Twig views.
	 * Called when explicitly invoking non-existent methods on a Core object.
	 * Not meant to be called directly.
	 *
	 * @example
	 * ```php
	 * $post = Timber\Post::get();
	 * update_post_meta($post->id, 'favorite_zep4_track', 'Black Dog');
	 * Timber::render('rock-n-roll.twig', array( 'post' => $post ));
	 * ```
	 * ```twig
	 * {# Since this method does not exist explicitly on the Post class,
	 *    it will dynamically dispatch the magic __call() method with an argument
	 *    of "favorite_zep4_track" #}
	 * <span>Favorite <i>Zeppelin IV</i> Track: {{ post.favorite_zep4_track() }}</span>
	 * ```
	 * @see https://secure.php.net/manual/en/language.oop5.overloading.php#object.call
	 * @see: https://github.com/fabpot/Twig/issues/2
	 * @param string the name of the method being called
	 * @return mixed the value of the meta field named `$field`, if truthy;
	 * false otherwise
	 */
	public function __call( string $field, $_ ) {
		if ( method_exists($this, 'meta') && $meta_value = $this->meta($field) ) {
			return $meta_value;
		}
		return false;
	}

	/**
	 * "Magic" getter for dynamic meta fields, for convenience in Twig views.
	 * Not meant to be called directly.
	 *
	 * @example
	 * ```php
	 * $post = Timber\Post::get();
	 * update_post_meta($post->id, 'favorite_darkside_track', 'Any Colour You Like');
	 * Timber::render('rock-n-roll.twig', array( 'post' => $post ));
	 * ```
	 * ```twig
	 * {# Since this property does not exist explicitly on the Post class,
	 *    it will dynamically dispatch the magic __get() method with an argument
	 *    of "favorite_darkside_track" #}
	 * <span>Favorite <i>Dark Side of the Moon</i> Track: {{ post.favorite_darkside_track }}</span>
	 * ```
	 * @see https://secure.php.net/manual/en/language.oop5.overloading.php#object.get
	 * @see: https://github.com/fabpot/Twig/issues/2
	 * @param string the name of the property being accessed
	 * @return mixed the value of the meta field, or the result of invoking
	 * `$field()` as a method with no arguments, or false if neither returns a
	 * truthy value
	 */
	public function __get( string $field ) {
		if ( method_exists($this, 'meta') && $meta_value = $this->meta($field) ) {
			return $this->$field = $meta_value;
		}
		if ( method_exists($this, $field) ) {
			return $this->$field = $this->$field();
		}
		return $this->$field = false;
	}

	/**
	 * Takes an array or object and adds the properties to the parent object.
	 *
	 * @example
	 * ```php
	 * $data = array( 'airplane' => '757-200', 'flight' => '5316' );
	 * $post = new Timber\Post();
	 * $post->import(data);
	 *
	 * echo $post->airplane; // 757-200
	 * ```
	 * @param array|object $info an object or array you want to grab data from to attach to the Timber object
	 */
	public function import( $info, $force = false, $only_declared_properties = false ) {
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
					if ( $only_declared_properties ) {
						if ( property_exists($this, $key) ) {
							$this->$key = $value;
						}
					} else {
						$this->$key = $value;
					}

				}
			}
		}
	}

	/**
	 * Updates metadata for the object.
	 *
	 * @deprecated 2.0.0 Use `update_metadata()` instead.
	 *
	 * @param string $key   The key of the meta field to update.
	 * @param mixed  $value The new value.
	 */
	public function update( $key, $value ) {
		Helper::deprecated( 'Timber\Core::update()', 'update_metadata()', '2.0.0' );
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
}
