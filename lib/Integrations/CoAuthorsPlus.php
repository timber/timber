<?php

namespace Timber\Integrations;

class CoAuthorsPlus {

	public static $prefer_gravatar = false;
	
	/**
	 * @codeCoverageIgnore
	 */
	public function __construct() {
		add_filter('timber/post/authors', array($this, 'authors'), 10, 2);
	}

	/**
	 * Filters {{ post.authors }} to return authors stored from Co-Authors Plus
	 * @since 1.1.4
	 * @param array $author
	 * @param Post $post
	 * @return array of User objects
	 */
	public function authors( $author, $post ) {
		$authors = array();
		$cauthors = get_coauthors($post->ID);
		foreach ( $cauthors as $author ) {
			$uid = $this->get_user_uid( $author );
			if ( $uid ) {
				$authors[] = new \Timber\User($uid);
			} else {
				$authors[] = new CoAuthorsPlusUser($author);
			}
		}
		return $authors;
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
		} else {
			return $cauthor->id;
		}
	}
}
