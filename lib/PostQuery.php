<?php

namespace Timber;

use Timber\Helper;
use Timber\Post;
use Timber\PostGetter;

/**
 * Class PostQuery
 *
 * Query for a collection of WordPress posts.
 *
 * This is the equivalent of using `WP_Query` in normal WordPress development.
 *
 * PostCollections are used directly in Twig templates to iterate through a collection of posts and
 * retrieve meta information about it.
 *
 * @api
 */
class PostQuery extends PostCollection {

	protected $userQuery;

	/**
	 * @var PostCollection|QueryIterator
	 */
	protected $queryIterator;

	protected $pagination = null;

	/**
	 * Query for a collection of WordPress posts.
	 *
	 * Refer to the official documentation for
	 * [WP_Query](https://developer.wordpress.org/reference/classes/wp_query/) for a list of all
	 * the arguments that can be used for the `$query` parameter.
	 *
	 * @api
	 * @example
	 * ```php
	 * // Get posts from default query
	 * $posts = new Timber\PostQuery();
	 *
	 * // Get custom posts collection with a query string
	 * $posts = new Timber\PostQuery( array(
	 *     'query' => 'post_type=article',
	 * ) );
	 *
	 * // Using the WP_Query argument format
	 * $posts = new Timber\PostQuery( array(
	 *     'query' => array(
	 *         'post_type'     => 'article',
	 *         'category_name' => 'sports',
	 *     ),
	 * ) );
	 *
	 * // Using a class map for $post_class
	 * $posts = new Timber\PostQuery( array(
	 *     'query' => array(
	 *         'post_type' => 'any',
	 *     ),
	 *     'post_class' => array(
	 *         'portfolio' => 'MyPortfolioClass',
	 *         'alert'     => 'MyAlertClass',
	 *     ),
	 * ) );
	 * ```
	 *
	 * @param array $args {
	 *     Optional. An array of arguments.
	 *
	 *     @type mixed        $query         Optional. A query string or an array of arguments for
	 *                                       `WP_Query`. Default `false`, which means that the
	 *                                       default WordPress query is used.
	 *     @type string|array $post_class    Optional. Class string or class map to wrap the post
	 *                                       objects in the collection. Default `Timber\Post`.
	 *     @type bool         $merge_default Optional. Whether to merge the arguments passed in
	 *                                       `query` with the default arguments that WordPress uses
	 *                                       for the current template. Default `false`.
	 * }
	 */
	public function __construct( $args = array() ) {
		// Backwards compatibility.
		if ( ! empty( $args ) && ! isset( $args['query'] ) ) {
			$args = array(
				'query' => $args,
			);

			Helper::deprecated(
				'Passing query arguments directly to PostQuery',
				'Put your query in an array with a "query" key',
				'2.0.0'
			);
		}

		$args = wp_parse_args( $args, array(
			'query'         => false,
			'merge_default' => false,
			'post_class'    => '\Timber\Post',
		) );

		if ( $args['merge_default'] ) {
			global $wp_query;

			// Merge query arguments with default query.
			$args['query'] = wp_parse_args( $args['query'], $wp_query->query_vars );
		}

		$this->userQuery     = $args['query'];
		$this->queryIterator = PostGetter::query_posts( $args['query'], $args['post_class'] );

		$posts = $this->queryIterator->get_posts();

		parent::__construct( $posts, $args['post_class'] );
	}

	/**
	 * @return mixed The query the user orignally passed to the pagination object.
	 */
	protected function get_query() {
		return $this->userQuery;
	}

	/**
	 * Get pagination for a post collection.
	 *
	 * Refer to the [Pagination Guide]({{< relref "../guides/pagination.md" >}}) for a detailed usage example.
	 *
	 * Optionally could be used to get pagination with custom preferences.
	 *
	 * @api
	 * @example
	 * ```twig
	 * {% if posts.pagination.prev %}
	 *     <a href="{{ posts.pagination.prev.link }}">Prev</a>
	 * {% endif %}
	 *
	 * <ul class="pages">
	 *     {% for page in posts.pagination.pages %}
	 *         <li>
	 *             <a href="{{ page.link }}" class="{{ page.class }}">{{ page.title }}</a>
	 *         </li>
	 *     {% endfor %}
	 * </ul>
	 *
	 * {% if posts.pagination.next %}
	 *     <a href="{{ posts.pagination.next.link }}">Next</a>
	 * {% endif %}
	 * ```
	 *
	 * @param array $prefs Optional. Custom preferences. Default `array()`.
	 *
	 * @return \Timber\Pagination object
	 */
	public function pagination( $prefs = array() ) {
		if ( !$this->pagination && is_a($this->queryIterator, 'Timber\QueryIterator') ) {
			$this->pagination = $this->queryIterator->get_pagination($prefs, $this->get_query());
		}

		return $this->pagination;
	}
}
