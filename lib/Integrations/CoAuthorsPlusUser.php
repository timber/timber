<?php

namespace Timber\Integrations;

class CoAuthorsPlusUser extends \Timber\User {
	
	/**
	 * @param object $author co-author object
	 */
	public function __construct( $author ) {
		parent::__construct($author);
	}

	/**
	 * @internal
	 * @param false|object $coauthor co-author object
	 */
	protected function init( $coauthor = false ) {
		$this->id = $coauthor->ID;
		$this->first_name = $coauthor->first_name;
		$this->last_name = $coauthor->last_name;
		$this->user_nicename = $coauthor->user_nicename;
		$this->description = $coauthor->description;

		/**
		 * @property string name
		 */
		$this->display_name = $coauthor->display_name;
		$this->_link = get_author_posts_url(null, $coauthor->user_nicename );

		// 96 is the default wordpress avatar size
		$avatar_url = get_the_post_thumbnail_url($this->id, 96);
		if ( CoAuthorsPlus::$prefer_gravatar || !$avatar_url ) {
			$avatar_url = get_avatar_url($coauthor->user_email);
		}
		if ( $avatar_url ) {
			$this->avatar = new \Timber\Image($avatar_url);
		}
	}
}
