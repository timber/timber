<?php

namespace Timber\Integrations;

use Timber\Factory\UserFactory;
use Timber\User;

class CoAuthorsPlus {

	public static $prefer_gravatar = false;
	
	/**
	 * @codeCoverageIgnore
	 */
	public function __construct() {
		add_filter('timber/post/authors', array($this, 'authors'), 10, 2);
		add_filter('timber/user/classmap', array($this, 'author_class'));
	}

	/**
	 * Filters {{ post.authors }} to return authors stored from Co-Authors Plus
	 * @since 1.1.4
	 * @param array        $_    The post's original author. Not used.
	 * @param \Timber\Post $post
	 * @return array of User objects
	 */
	public function authors( $_, $post ) {
		$factory = new UserFactory();
		$coauthors = get_coauthors($post->ID);

		// Convert guest authors into something Factories know how to deal with
		$coauthors = array_map(function( object $author) {
			if ($author instanceof \stdclass) {
				$class = apply_filters('timber/user/classmap', CoAuthorsPlusUser::class, $author);
				return $class::from_guest_author($author);
			}

			return $author;
		}, $coauthors);

		return $factory->from($coauthors);
	}

	/**
	 * Filter the author class based on Guest Author status, etc.
	 * 
	 * @internal
	 * @param \WP_User $author the user to Timberize
	 * @return string
	 */
	public function author_class( $author ) {
		// TODO rename this var; it's not really the ID, but its truthiness, that we care about here.
		// Also add a comment explaining the reasoning here, as it's not immediately clear why you'd want this.
		$uid = $this->get_user_uid( $author );

		return $uid ? User::class : CoAuthorsPlusUser::class;
	}

	/**
	 * return the user id for normal authors
	 * the user login for guest authors if it exists and self::prefer_users == true
	 * or null
	 * @internal
	 * @param object $cauthor
	 * @return int|string|null
	 */
	protected function get_user_uid( $cauthor ) {
		// if is guest author
		if( is_object($cauthor) && isset($cauthor->type) && $cauthor->type == 'guest-author'){
			// if have have a linked user account
			global $coauthors_plus;
			if( !$coauthors_plus->force_guest_authors && isset($cauthor->linked_account) && !empty($cauthor->linked_account ) ){
				return $cauthor->linked_account;
			} else {
				return null;
			}
		} elseif ( is_object($cauthor) ) {
			return $cauthor->ID;
		}
	}
}
