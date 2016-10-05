<?php

namespace Timber;

use Timber\Helper;
use Timber\Post;
use Timber\PostGetter;

// Exit if accessed directly
if ( !defined('ABSPATH') ) {
	exit;
}

/**
 * A PostQuery allows a user to query for a Collection of WordPress Posts.
 * PostCollections are used directly in Twig templates to iterate through and retrieve
 * meta information about the collection of posts
 * @api
 */
class PostQuery extends PostCollection {

	//maintain reference to $query object to generate pagination
	
	protected $userQuery;
	protected $queryIterator;
	protected $pagination = null;

	public function __construct( $query = false, $post_class = '\Timber\Post' ) {
		$this->userQuery = $query;
		$this->queryIterator = PostGetter::query_posts($query, $post_class);

		$posts = $this->queryIterator->get_posts();

		parent::__construct($posts, $post_class);
	}

	protected function get_query() {
		return $this->userQuery;
	}

	/**
	 * Set pagination for the collection. Optionally could be used to get pagination with custom preferences.
	 *
	 * @param 	array $prefs
	 * @return 	Timber\Pagination object
	 */
	public function pagination( $prefs = array() ) {
		if ( !$this->pagination && is_a($this->queryIterator, 'Timber\QueryIterator')) {
			$this->pagination = $this->queryIterator->get_pagination($prefs, $this->get_query());
		}
		return $this->pagination;
	}

}
