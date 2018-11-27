<?php

namespace Timber;

use Timber\Core;
use Timber\CoreInterface;

use Timber\URLHelper;

use Timber\Image;

/**
 * Class User
 *
 * A user object represents a WordPress user.
 *
 * The currently logged-in user will be available as `{{ user }}` in your Twig files through the
 * global context. If a user is not logged in, it will be `false`. This will make it possible for
 * you to check if a user is logged by checking for `user` instead of calling `is_user_logged_in()`
 * in your Twig templates.
 *
 * @api
 * @example
 * ```twig
 * {% if user %}
 *     Hello {{ user.name }}
 * {% endif %}
 * ```
 *
 * The difference between a logged-in user and a post author:
 *
 * ```php
 * $context = Timber::context();
 *
 * Timber::render( 'single.twig', $context );
 * ```
 * ```twig
 * <p class="current-user-info">Your name is {{ user.name }}</p>
 * <p class="article-info">This article is called "{{ post.title }}"
 *     and it’s by {{ post.author.name }}</p>
 * ```
 * ```html
 * <p class="current-user-info">Your name is Jesse Eisenberg</p>
 * <p class="article-info">This article is called "Consider the Lobster"
 *     and it’s by David Foster Wallace</p>
 * ```
 */
class User extends Core implements CoreInterface, MetaInterface {

	public $object_type = 'user';
	public static $representation = 'user';

	public $_link;

	/**
	 * @api
	 * @var string The description from WordPress
	 */
	public $description;

	/**
	 * @api
	 * @var string
	 */
	public $display_name;

	/**
	 * @api
	 * @var string|Image The URL of the author's avatar
	 */
	public $avatar;

	/**
	 * @api
	 * @var string The first name of the user
	 */
	public $first_name;

	/**
	 * @api
	 * @var string The last name of the user
	 */
	public $last_name;

	/**
	 * @api
	 * @var int The ID from WordPress
	 */
	public $id;

	/**
	 * @api
	 * @var string
	 */
	public $user_nicename;

	/**
	 * @api
	 * @param object|int|bool $uid
	 */
	public function __construct( $uid = false ) {
		$this->init($uid);
	}

	/**
	 * @api
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
		$this->custom = $this->get_meta_values();
		$this->import($this->custom, false, true);
	}


	/**
	 * Retrieves the custom (meta) data on a user and returns it.
	 *
	 * @internal
	 * @return array|null
	 */
	protected function get_meta_values() {
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
			$um = apply_filters( 'timber/user/pre_get_meta_values', $um, $this->ID, $this );

			/**
			 * Filters user meta data before it is fetched from the database.
			 *
			 * @deprecated 2.0.0, use `timber/user/pre_get_meta_values`
			 */
			$um = apply_filters_deprecated(
				'timber_user_get_meta_pre',
				array( $um, $this->ID, $this ),
				'2.0.0',
				'timber/user/pre_get_meta_values'
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
			$custom = apply_filters( 'timber/user/get_meta_values', $custom, $this->ID, $this );

			/**
			 * Filters user meta data fetched from the database.
			 *
			 * @deprecated 2.0.0, use `timber/user/get_meta_values`
			 */
			$custom = apply_filters_deprecated(
				'timber_user_get_meta',
				array( $custom, $this->ID, $this ),
				'2.0.0',
				'timber/user/get_meta_values'
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
	 * Gets a user meta value.
	 *
	 * Returns a meta value for a user that’s saved in the user meta database table.
	 *
	 * @api
	 *
	 * @param string $field_name The field name for which you want to get the value.
	 * @param array  $args       An array of arguments for getting the meta value. Third-party
	 *                           integrations can use this argument to make their API arguments
	 *                           available in Timber. Default empty.
	 * @return mixed The meta field value.
	 */
	public function meta( $field_name, $args = array() ) {
		/**
		 * Filters a user meta field before it is fetched from the database.
		 *
		 * @todo Add description, example
		 *
		 * @see \Timber\User::meta()
		 * @since 2.0.0
		 *
		 * @param mixed        $value      The field value. Passing a non-null value will skip
		 *                                 fetching the value from the database, returning the
		 *                                 filtered value instead. Default null.
		 * @param int          $user_id    The user ID.
		 * @param string       $field_name The name of the meta field to get the value for.
		 * @param array        $args       An array of arguments.
		 * @param \Timber\User $user       The user object.
		 */
		$value = apply_filters(
			'timber/user/pre_meta',
			null,
			$this->ID,
			$field_name,
			$args,
			$this
		);

		/**
		 * Filters a user meta field before it is fetched from the database.
		 *
		 * @deprecated 2.0.0, use `timber/user/pre_meta`
		 */
		$value = apply_filters_deprecated(
			'timber_user_get_meta_field_pre',
			array( $value, $this->ID, $field_name, $this ),
			'2.0.0',
			'timber/user/pre_meta'
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
		 * @param array        $args       An array of arguments.
		 * @param \Timber\User $user       The user object.
		 */
		$value = apply_filters(
			'timber/user/meta',
			$value,
			$this->ID,
			$field_name,
			$args,
			$this
		);

		/**
		 * Filters the value for a user meta field.
		 *
		 * @deprecated 2.0.0, use `timber/user/meta`
		 */
		$value = apply_filters_deprecated(
			'timber_user_get_meta_field',
			array( $value, $this->ID, $field_name, $this ),
			'2.0.0',
			'timber/user/meta'
		);

		return $value;
	}

	/**
	 * Gets a user meta value.
	 *
	 * @api
	 * @deprecated 2.0.0, use `{{ user.meta('field_name') }}` instead.
	 * @see \Timber\User::meta()
	 *
	 * @param string $field_name The field name for which you want to get the value.
	 * @return mixed The meta field value.
	 */
	public function get_field( $field_name = null ) {
		Helper::deprecated(
			"{{ user.get_field('field_name') }}",
			"{{ user.meta('field_name') }}",
			'2.0.0'
		);

		return $this->meta( $field_name );
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
	 * Gets a user meta value.
	 *
	 * @api
	 * @deprecated 2.0.0, use `{{ user.meta('field_name') }}` instead.
	 *
	 * @param string $field_name The field name for which you want to get the value.
	 * @return mixed The meta field value.
	 */
	public function get_meta_field( $field_name ) {
		Helper::deprecated(
			"{{ user.get_meta_field('field_name') }}",
			"{{ user.meta('field_name') }}",
			'2.0.0'
		);

		return $this->meta( $field_name );
	}

	/**
	 * Gets a user meta value.
	 *
	 * @api
	 * @deprecated 2.0.0, use `{{ user.meta('field_name') }}` instead.
	 *
	 * @param string $field_name The field name for which you want to get the value.
	 * @return mixed The meta field value.
	 */
	public function get_meta( $field_name ) {
		Helper::deprecated(
			"{{ user.get_meta('field_name') }}",
			"{{ user.meta('field_name') }}",
			'2.0.0'
		);

		return $this->meta( $field_name );
	}
}
