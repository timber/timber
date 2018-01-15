<?php

namespace Timber;

use Timber\Core;
use Timber\CoreInterface;

use Timber\URLHelper;

use Timber\Image;

/**
 * This is used in Timber to represent users retrived from WordPress. You can call `$my_user = new Timber\User(123);` directly, or access it through the `{{ post.author }}` method.
 * @example
 * ```php
 * $context['current_user'] = new Timber\User();
 * $context['post'] = new Timber\Post();
 * Timber::render('single.twig', $context);
 * ```
 * ```twig
 * <p class="current-user-info">Your name is {{ current_user.name }}</p>
 * <p class="article-info">This article is called "{{ post.title }}" and it's by {{ post.author.name }}
 * ```
 * ```html
 * <p class="current-user-info">Your name is Jesse Eisenberg</p>
 * <p class="article-info">This article is called "Consider the Lobster" and it's by David Foster Wallace
 * ```
 */
class User extends Core implements CoreInterface {

	public $object_type = 'user';
	public static $representation = 'user';

	public $_link;

	/**
	 * @api
	 * @var string The description from WordPress
	 */
	public $description;
	public $display_name;

	/**
	 * @api
	 * @var string|Image The URL of the author's avatar
	 */
	public $avatar;

	/**
	 * @api
	 * @var  string The first name of the user
	 */
	public $first_name;

	/**
	 * @api
	 * @var  string The last name of the user
	 */
	public $last_name;

	/**
	 * @api
	 * @var int The ID from WordPress
	 */
	public $id;
	public $user_nicename;

	/**
	 * @param object|int|bool $uid
	 */
	public function __construct( $uid = false ) {
		$this->init($uid);
	}

	/**
	 * @example
	 * ```twig
	 * This post is by {{ post.author }}
	 * ```
	 * ```html
	 * This post is by Jared Novack
	 * ```
	 *
	 * @return string a fallback for Timber\User::name()
	 */
	public function __toString() {
		$name = $this->name();
		if ( strlen($name) ) {
			return $name;
		}
		if ( strlen($this->name) ) {
			return $this->name;
		}
		return '';
	}

	/**
	 * @internal
	 * @param string 	$field
	 * @param mixed 	$value
	 */
	public function __set( $field, $value ) {
		if ( 'name' === $field ) {
			$this->display_name = $value;
		}
		$this->$field = $value;
	}

	/**
	 * @internal
	 * @param object|int|bool $uid The user ID to use
	 */
	protected function init( $uid = false ) {
		if ( $uid === false ) {
			$uid = get_current_user_id();
		}
		if ( is_object($uid) || is_array($uid) ) {
			$data = $uid;
			if ( is_array($uid) ) {
				$data = (object) $uid;
			}
			$uid = $data->ID;
		}
		if ( is_numeric($uid) ) {
			$data = get_userdata($uid);
		} elseif ( is_string($uid) ) {
			$data = get_user_by('login', $uid);
		}
		if ( isset($data) && is_object($data) ) {
			if ( isset($data->data) ) {
				$this->import($data->data);
			} else {
				$this->import($data);
			}
		}
		unset($this->user_pass);
		$this->id = $this->ID;
		$this->name = $this->name();
		$this->avatar = new Image(get_avatar_url($this->id));
		$this->custom = $this->get_custom();
		$this->import($this->custom, false, true);
	}


	/**
	 * Retrieves the custom (meta) data on a user and returns it.
	 *
	 * @internal
	 * @return array|null
	 */
	protected function get_custom() {
		if ( $this->ID ) {
			$um = array();

			/**
			 * Filters user meta data before it is fetched from the database.
			 *
			 * @since 2.0.0
			 *
			 * @param array        $user_meta User meta data. Passing a non-empty array will skip
			 *                                fetching meta values from the database, returning the
			 *                                filtered value instead. Default `array()`.
			 * @param int          $user_id   The user ID.
			 * @param \Timber\User $user      The user object.
			 */
			$um = apply_filters( 'timber/user/pre_get_meta', $um, $this->ID, $this );

			/**
			 * Filters user meta data before it is fetched from the database.
			 *
			 * @deprecated 2.0.0, use `timber/user/pre_get_meta`
			 */
			$um = apply_filters_deprecated(
				'timber_user_get_meta_pre',
				array( $um, $this->ID, $this ),
				'2.0.0',
				'timber/user/pre_get_meta'
			);

			if ( empty($um) ) {
				$um = get_user_meta($this->ID);
			}
			$custom = array();
			foreach ( $um as $key => $value ) {
				if ( is_array($value) && count($value) === 1 ) {
					$value = $value[0];
				}
				$custom[ $key ] = maybe_unserialize($value);
			}

			/**
			 * Filters user meta data fetched from the database.
			 *
			 * @since 2.0.0
			 *
			 * @param array        $user_meta User meta data fetched from the database.
			 * @param int          $user_id   The user ID.
			 * @param \Timber\User $user      The user object.
			 */
			$custom = apply_filters( 'timber/user/get_meta', $custom, $this->ID, $this );

			/**
			 * Filters user meta data fetched from the database.
			 *
			 * @deprecated 2.0.0, use `timber/user/get_meta`
			 */
			$custom = apply_filters_deprecated(
				'timber_user_get_meta',
				array( $custom, $this->ID, $this ),
				'2.0.0',
				'timber/user/get_meta'
			);

			return $custom;
		}
		return null;
	}

	/**
	 * Get the URL of the user's profile
	 *
	 * @api
	 * @return string http://example.org/author/lincoln
	 */
	public function link() {
		if ( ! $this->_link ) {
			$this->_link = user_trailingslashit(get_author_posts_url($this->ID));
		}
		return $this->_link;
	}

	/**
	 * @param string $field_name
	 * @return mixed
	 */
	public function meta( $field_name ) {
		$value = null;

		/**
		 * Filters a user meta field before it is fetched from the database.
		 *
		 * @see \Timber\User::meta()
		 * @since 2.0.0
		 *
		 * @param mixed        $value      The field value. Passing a non-null value will skip
		 *                                 fetching the value from the database, returning the
		 *                                 filtered value instead. Default `null`.
		 * @param int          $user_id    The user ID
		 * @param string       $field_name The name of the meta field to get the value for.
		 * @param \Timber\User $user       The user object.
		 */
		$value = apply_filters( 'timber/user/pre_get_meta_field', $value, $this->ID, $field_name, $this );

		/**
		 * Filters a user meta field before it is fetched from the database.
		 *
		 * @deprecated 2.0.0, use `timber/user/pre_get_meta_field`
		 */
		$value = apply_filters_deprecated(
			'timber_user_get_meta_field_pre',
			array( $value, $this->ID, $field_name, $this ),
			'2.0.0',
			'timber/user/pre_get_meta_field'
		);

		if ( null === $value ) {
			$value = get_user_meta($this->ID, $field_name, true);
		}

		/**
		 * Filters the value for a user meta field.
		 *
		 * @see \Timber\User::meta()
		 * @since 2.0.0
		 *
		 * @param mixed        $value      The field value.
		 * @param int          $user_id    The user ID.
		 * @param string       $field_name The name of the meta field to get the value for.
		 * @param \Timber\User $user       The user object.
		 */
		$value = apply_filters( 'timber/user/get_meta_field', $value, $this->ID, $field_name, $this );

		/**
		 * Filters the value for a user meta field.
		 *
		 * @deprecated 2.0.0, use `timber/user/get_meta_field`
		 */
		$value = apply_filters_deprecated(
			'timber_user_get_meta_field',
			array( $value, $this->ID, $field_name, $this ),
			'2.0.0',
			'timber/user/get_meta_field'
		);

		return $value;
	}

	/**
	 * Get the name of the User
	 *
	 * @api
	 * @return string the human-friendly name of the user (ex: "Buster Bluth")
	 */
	public function name() {
		/**
		 * Filters the name of a user.
		 *
		 * @since 1.1.4
		 *
		 * @param string       $name The name of the user. Default `display_name`.
		 * @param \Timber\User $user The user object.
		 */
		return apply_filters('timber/user/name', $this->display_name, $this);
	}

	/**
	 * Get the relative path to the user's profile
	 *
	 * @api
	 * @return string ex: /author/lincoln
	 */
	public function path() {
		return URLHelper::get_rel_url($this->link());
	}

	/**
	 * @api
	 * @return string ex baberaham-lincoln
	 */
	public function slug() {
		return $this->user_nicename;
	}

	/**
	 * DEPRECATION ZONE
	 */

	/**
	 * @deprected since 2.0
	 * @param string $field_name
	 * @return mixed
	 */
	public function get_meta_field( $field_name ) {
		return $this->meta($field_name);
	}

	/**
	 * @deprected since 2.0
	 * @internal
	 * @param string $field_name
	 * @return null
	 */
	public function get_meta( $field_name ) {
		return $this->get_meta_field($field_name);
	}
}
