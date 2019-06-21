<?php

namespace Timber;

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
	 * @var string A URL to an avatar that overrides anything from Gravatar, etc.
	 */
	public $avatar_override;

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
	 * Meta values.
	 *
	 * With this property you can check which meta values exist for a user, but you can’t access the
	 * values through this property. Use `{{ user.meta('field_name') }}` or
	 * `{{ user.raw_meta('field_name') }}` to get the values for a custom field.
	 *
	 * @api
	 * @since 2.0.0
	 * @see User::meta()
	 * @see User::raw_meta()
	 * @var array Storage for a user’s meta data.
	 */
	protected $custom = array();

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
	 * @param object|int|bool|string $uid
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
		return $this->name();
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

			if ( isset($data->roles) ) {
				$this->roles = $this->get_roles($data->roles);
			}
		}
		unset($this->user_pass);
		$this->id = $this->ID;
		$this->custom = $this->get_meta_values( $this->ID );
		$this->import($this->custom, false, true);
	}

	/**
	 * Retrieves the custom (meta) data on a user and returns it.
	 *
	 * @internal
	 *
	 * @param int $user_id
	 * @return array
	 */
	protected function get_meta_values( $user_id ) {
		$user_meta = array();

		/**
		 * Filters user meta data before it is fetched from the database.
		 *
		 * Timber loads all meta values into the user object on initialization. With this filter,
		 * you can disable fetching the meta values through the default method, which uses
		 * `get_user_meta()`, by returning `false` or a non-empty array.
		 *
		 * @example
		 * ```php
		 * // Disable fetching meta values.
		 * add_filter( 'timber/user/pre_get_meta_values', '__return_false' );
		 *
		 * // Add your own meta values.
		 * add_filter( 'timber/user/pre_get_meta_values', function( $user_meta, $user_id, $user ) {
		 *     $user_meta = array(
		 *         'custom_data_1' => 73,
		 *         'custom_data_2' => 274,
		 *     );
		 *
		 *     return $user_meta;
		 * }, 10, 3 );
		 * ```
		 *
		 * @since 2.0.0
		 *
		 * @param array        $user_meta An array of custom meta values. Passing `false` or a
		 *                                non-empty array will skip fetching meta values from the
		 *                                database, returning the filtered value instead. Default
		 *                                `array()`.
		 * @param int          $user_id   The user ID.
		 * @param \Timber\User $user      The user object.
		 */
		$user_meta = apply_filters( 'timber/user/pre_get_meta_values', $user_meta, $user_id, $this );

		/**
		 * Filters user meta data before it is fetched from the database.
		 *
		 * @deprecated 2.0.0, use `timber/user/pre_get_meta_values`
		 */
		$user_meta = apply_filters_deprecated(
			'timber_user_get_meta_pre',
			array( $user_meta, $user_id, $this ),
			'2.0.0',
			'timber/user/pre_get_meta_values'
		);

		// Load all meta data when it wasn’t filtered before.
		if ( false !== $user_meta && empty( $user_meta ) ) {
			$user_meta = get_user_meta($user_id);
		}

		if ( ! empty( $user_meta ) ) {
			foreach ( $user_meta as $key => $value ) {
				if ( is_array($value) && count($value) === 1 ) {
					$value = $value[0];
				}
				$user_meta[ $key ] = maybe_unserialize($value);
			}
		}

		/**
		 * Filters user meta data fetched from the database.
		 *
		 * Timber loads all meta values into the user object on initialization. With this filter,
		 * you can change meta values after they were fetched from the database.
		 *
		 * @example
		 * ```php
		 * add_filter( 'timber/user/get_meta_values', function( $user_meta, $user_id, $user ) {
		 *     if ( 123 === $user_id ) {
		 *         // Do something special.
		 *         $user_meta['foo'] = $user_meta['foo'] . ' bar';
		 *     }
		 *
		 *     return $user_meta;
		 * }, 10, 3 );
		 * ```
		 *
		 * @since 2.0.0
		 *
		 * @param array        $user_meta User meta data fetched from the database.
		 * @param int          $user_id   The user ID.
		 * @param \Timber\User $user      The user object.
		 */
		$user_meta = apply_filters( 'timber/user/get_meta_values', $user_meta, $user_id, $this );

		/**
		 * Filters user meta data fetched from the database.
		 *
		 * @deprecated 2.0.0, use `timber/user/get_meta_values`
		 */
		$user_meta = apply_filters_deprecated(
			'timber_user_get_meta',
			array( $user_meta, $user_id, $this ),
			'2.0.0',
			'timber/user/get_meta_values'
		);

		// Ensure proper return value.
		if ( empty( $user_meta ) ) {
			$user_meta = array();
		}

		return $user_meta;
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
	 * @return mixed The meta field value. Null if no value could be found.
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
	 * Gets a user meta value directly from the database.
	 *
	 * Returns a raw meta value for a user that’s saved in the term meta database table. Be aware
	 * that the value can still be filtered by plugins.
	 *
	 * @api
	 * @since 2.0.0
	 * @param string $field_name The field name for which you want to get the value.
	 * @return null|mixed The meta field value. Null if no value could be found.
	 */
	public function raw_meta( $field_name ) {
		if ( isset( $this->custom[ $field_name ] ) ) {
			return $this->custom[ $field_name ];
		}

		return null;
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
