<?php

namespace Timber;

use Timber\Core;
use Timber\CoreInterface;

use Timber\URLHelper;

/**
 * This is used in Timber to represent users retrived from WordPress. You can call `$my_user = new TimberUser(123);` directly, or access it through the `{{ post.author }}` method.
 * @example
 * ```php
 * $context['current_user'] = new TimberUser();
 * $context['post'] = new TimberPost();
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
	 * @var string The URL of the author's avatar
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
	 * @param int|bool $uid
	 */
	function __construct( $uid = false ) {
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
	function __toString() {
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
	 * @param string $field_name
	 * @return null
	 */
	function get_meta( $field_name ) {
		return $this->get_meta_field($field_name);
	}

	/**
	 * @internal
	 * @param string 	$field
	 * @param mixed 	$value
	 */
	function __set( $field, $value ) {
		if ( $field == 'name' ) {
			$this->display_name = $value;
		}
		$this->$field = $value;
	}

	/**
	 * @internal
	 * @param int|bool $uid The user ID to use
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
		}
		$this->id = $this->ID;
		$this->name = $this->name();
		$this->avatar = get_avatar_url($this->id);
		$custom = $this->get_custom();
		$this->import($custom);
	}

	/**
	 * @param string $field_name
	 * @return mixed
	 */
	function get_meta_field( $field_name ) {
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
	function get_custom() {
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
			$this->_link = untrailingslashit(get_author_posts_url($this->ID));
		}
		return $this->_link;
	}

	/**
	 * @api
	 * @return string the human-friendly name of the user (ex: "Buster Bluth")
	 */
	function name() {
		return $this->display_name;
	}

	/**
	 * @param string $field_name
	 * @return mixed
	 */
	function meta( $field_name ) {
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
}