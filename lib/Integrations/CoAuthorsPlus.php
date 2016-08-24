<?php

namespace Timber\Integrations;

class CoAuthorsPlus {

	public function __construct(){
		//add_filter('timber/user/name', array($this, 'name'), 10, 2);
		add_filter('timber/post/authors', array($this, 'authors'), 10, 2);
	}

	function authors($author, $post) {
		$authors = array($author);
		$cauthors = get_coauthors( $post->ID );
		foreach($cauthors as $author){
			$authors[] = new \Timber\User($author);
		}
		return $authors;
	}

}