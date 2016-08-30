<?php

namespace Timber\Integrations;

class CoAuthorsPlus {

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
			$authors[] = new \Timber\User($author);
		}
		return $authors;
	}

}