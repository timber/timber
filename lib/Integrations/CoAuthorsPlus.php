<?php

namespace Timber\Integrations;

class CoAuthorsPlus {

	public function __construct(){
		add_filter('timber/post/authors', array($this, 'authors'), 10, 2);
	}

	public function authors($author, $post) {
		$authors = array();
		$cauthors = get_coauthors( $post->ID );
		foreach($cauthors as $author){
			$authors[] = new \Timber\User($author);
		}
		return $authors;
	}

}