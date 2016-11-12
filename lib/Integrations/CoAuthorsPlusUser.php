<?php

namespace Timber\Integrations;

class CoAuthorsPlusUser extends \Timber\User
{
	public function __construct( $author )
	{
		$uid = $this->get_coauthor_uid($author);
		parent::__construct($uid);
	}

	/**
	 * @internal
	 * @param false|object|string $coauthor co-author object
	 */
	protected function init( $coauthor = false ){
		if ( is_object($coauthor) ){
			$this->id = $coauthor->ID;
			$this->first_name = $coauthor->first_name;
			$this->last_name = $coauthor->last_name;
			$this->user_nicename = $coauthor->user_nicename;
			$this->description = $coauthor->description;

			/**
			 * @property string name
			 */
			$this->name = $coauthor->display_name;
			$this->_link = get_author_posts_url(null, $coauthor->user_nicename );

			// 96 is the default wordpress avatar size
			$avatar_url = get_the_post_thumbnail_url($this->id, 96);
			if( CoAuthorsPlus::$prefer_gravatar || !$avatar_url ){
				$avatar_url = get_avatar_url($coauthor->user_email);
			}
			if( $avatar_url ){
				$this->avatar = new \Timber\Image($avatar_url);
			}
		} else {
			parent::init($coauthor);
		}
	}

	/**
	 * return $uid used by init method
	 * @internal
	 * @param object $cauthor
	 * @return string|object
	 */
	protected function get_coauthor_uid( $cauthor )
	{
		if( CoAuthorsPlus::$prefer_user && isset($cauthor->linked_account) && !empty($cauthor->linked_account ) ){
			return $cauthor->linked_account;
		} else {
			return $cauthor;
		}
	}
}