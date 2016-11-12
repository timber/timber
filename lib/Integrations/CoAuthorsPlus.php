<?php

namespace Timber\Integrations;

class CoAuthorsPlus {

	public static $prefer_gravatar = false;
	public static $prefer_user = false;
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
			if( is_object($author) && isset($author->type) && $author->type == 'guest-author' ){
				$authors[] = new CoAuthorsPlusUser($author);
			} else {
				$authors[] = new \Timber\User($author);
			}
		}
		return $authors;
	}
}