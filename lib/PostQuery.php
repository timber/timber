<?php

namespace Timber;

use Timber\Helper;
use Timber\Post;
use Timber\PostGetter;

/**
 * A PostQuery allows a user to query for a Collection of WordPress Posts.
 * PostCollections are used directly in Twig templates to iterate through and retrieve
 * meta information about the collection of posts
 * @api
 * @package Timber
 */
class PostQuery extends PostCollection {
	/**
	 * Found posts.
	 *
	 * The total amount of posts found for this query. Will be `0` if you used `no_found_rows` as a
	 * query parameter. Will be `null` if you passed in an existing collection of posts.
	 *
	 * @api
	 * @since 1.11.1
	 * @var int The amount of posts found in the query.
	 */
	public $found_posts = null;

	protected $userQuery;
	protected $queryIterator;
	protected $pagination = null;

	/**
	 * @param mixed   	$query
	 * @param string 	$post_class
	 */
	public function __construct( $query = false, $post_class = '\Timber\Post' ) {
		$this->userQuery = $query;
		$this->queryIterator = PostGetter::query_posts($query, $post_class);

		if ( $this->queryIterator instanceof QueryIterator ) {
			$this->found_posts = $this->queryIterator->found_posts();
		}

		$posts = $this->queryIterator->get_posts();

		parent::__construct($posts, $post_class);
	}

	/**
	 * @return mixed the query the user orignally passed
	 * to the pagination object
	 */
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
		if ( !$this->pagination && is_a($this->queryIterator, 'Timber\QueryIterator') ) {
			$this->pagination = $this->queryIterator->get_pagination($prefs, $this->get_query());
		}
		return $this->pagination;
	}

}
