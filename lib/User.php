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
	 * @var string A URL to an avatar that overrides anything from Gravatar, etc.
	 */
	public $avatar_override;

	/**
	 * @api
	 * @var string The description from WordPress
	 */
	public $description;
	public $display_name;

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
	 * The roles the user is part of.
	 *
	 * @api
	 * @since 1.8.5
	 *
	 * @var array
	 */
	protected $roles;

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
	 * @return string a fallback for TimberUser::name()
	 */
	public function __toString() {
		return $this->name();
	}

	/**
	 * @internal
	 * @param string $field_name
	 * @return null
	 */
	public function get_meta( $field_name ) {
		return $this->get_meta_field($field_name);
	}

	/**
	 * @internal
	 * @param string 	$field
	 * @param mixed 	$value
	 */
	public function __set( $field, $value ) {
		if ( $field == 'name' ) {
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
		} else if ( is_string($uid) ) {
			$data = get_user_by('login', $uid);
		}
		if ( isset($data) && is_object($data) ) {
			if ( isset($data->data) ) {
				$this->import($data->data);
			} else {
				$this->import($data);
			}

			if ( isset($data->roles) ) {
				$this->roles = $this->get_roles($data->roles);
			}
		}
		unset($this->user_pass);
		$this->id = $this->ID;
		$this->name = $this->name();
		$custom = $this->get_custom();
		$this->import($custom);
	}

	/**
	 * @param string $field_name
	 * @return mixed
	 */
	public function get_meta_field( $field_name ) {
		$value = null;
		$value = apply_filters('timber_user_get_meta_field_pre', $value, $this->ID, $field_name, $this);
		if ( $value === null ) {
			$value = get_user_meta($this->ID, $field_name, true);
		}
		$value = apply_filters('timber_user_get_meta_field', $value, $this->ID, $field_name, $this);
		return $value;
	}

	/**
	 * @return array|null
	 */
	public function get_custom() {
		if ( $this->ID ) {
			$um = array();
			$um = apply_filters('timber_user_get_meta_pre', $um, $this->ID, $this);
			if ( empty($um) ) {
				$um = get_user_meta($this->ID);
			}
			$custom = array();
			foreach ( $um as $key => $value ) {
				if ( is_array($value) && count($value) == 1 ) {
					$value = $value[0];
				}
				$custom[$key] = maybe_unserialize($value);
			}
			$custom = apply_filters('timber_user_get_meta', $custom, $this->ID, $this);
			return $custom;
		}
		return null;
	}

	/**
	 * @api
	 * @return string http://example.org/author/lincoln
	 */
	public function link() {
		if ( !$this->_link ) {
			$this->_link = user_trailingslashit(get_author_posts_url($this->ID));
		}
		return $this->_link;
	}

	/**
	 * @api
	 * @return string the human-friendly name of the user (ex: "Buster Bluth")
	 */
	public function name() {
		return apply_filters('timber/user/name', $this->display_name, $this);
	}

	/**
	 * @param string $field_name
	 * @return mixed
	 */
	public function meta( $field_name ) {
		return $this->get_meta_field($field_name);
	}

	/**
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
	 * Creates an associative array with user role slugs and their translated names.
	 *
	 * @internal
	 * @since 1.8.5
	 * @param array $roles user roles.
	 * @return array|null
	 */
	protected function get_roles( $roles ) {
		if ( empty($roles) ) {
			// @codeCoverageIgnoreStart
			return null;
			// @codeCoverageIgnoreEnd
		}

		$wp_roles = wp_roles();
		$names    = $wp_roles->get_names();

		$values = array();

		foreach ( $roles as $role ) {
			$name = $role;
			if ( isset($names[ $role ]) ) {
				$name = translate_user_role($names[ $role ]);
			}
			$values[ $role ] = $name;
		}

		return $values;
	}

	/**
	 * Gets the user roles.
	 * Roles shouldn’t be used to check whether a user has a capability. Use roles only for
	 * displaying purposes. For example, if you want to display the name of the subscription a user
	 * has on the site behind a paywall.
	 *
	 * If you want to check for capabilities, use `{{ user.can('capability') }}`. If you only want
	 * to check whether a user is logged in, you can use `{% if user %}`.
	 *
	 * @api
	 * @since 1.8.5
	 * @example
	 * ```twig
	 * <h2>Role name</h2>
	 * {% for role in post.author.roles %}
	 *     {{ role }}
	 * {% endfor %}
	 * ```
	 * ```twig
	 * <h2>Role name</h2>
	 * {{ post.author.roles|join(', ') }}
	 * ```
	 * ```twig
	 * {% for slug, name in post.author.roles %}
	 *     {{ slug }}
	 * {% endfor %}
	 * ```
	 *
	 * @return array|null
	 */
	public function roles() {
		return $this->roles;
	}

	/**
	 * Checks whether a user has a capability.
	 *
	 * Don’t use role slugs for capability checks. While checking against a role in place of a
	 * capability is supported in part, this practice is discouraged as it may produce unreliable
	 * results. This includes cases where you want to check whether a user is registered. If you
	 * want to check whether a user is a Subscriber, use `{{ user.can('read') }}`. If you only want
	 * to check whether a user is logged in, you can use `{% if user %}`.
	 *
	 * @api
	 * @since 1.8.5
	 *
	 * @param string $capability The capability to check.
	 *
	 * @example
	 * Give moderation users another CSS class to style them differently.
	 *
	 * ```twig
	 * <span class="comment-author {{ comment.author.can('moderate_comments') ? 'comment-author--is-moderator }}">
	 *     {{ comment.author.name }}
	 * </span>
	 * ```
	 *
	 * @return bool Whether the user has the capability.
	 */
	public function can( $capability ) {
		return user_can($this->ID, $capability);
	}

	/**
	 * Gets a user’s avatar URL.
	 *
	 * @api
	 * @since 1.9.1
	 * @example
	 * Get a user avatar with a width and height of 150px:
	 *
	 * ```twig
	 * <img src="{{ post.author.avatar({ size: 150 }) }}">
	 * ```
	 *
	 * @param null|array $args Parameters for
	 *                         [`get_avatar_url()`](https://developer.wordpress.org/reference/functions/get_avatar_url/).
	 * @return string|\Timber\Image The avatar URL.
	 */
	public function avatar( $args = null ) {
		if ( $this->avatar_override ) {
			return $this->avatar_override;
		}

		return new Image( get_avatar_url( $this->id, $args ) );
	}
}
