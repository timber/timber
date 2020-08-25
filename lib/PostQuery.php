<?php

namespace Timber;

use ArrayObject;
use JsonSerializable;
use WP_Post;
use WP_Query;

/**
 * Class PostQuery
 *
 * Query for a collection of WordPress posts.
 *
 * This is the equivalent of using `WP_Query` in normal WordPress development.
 *
 * PostQuery is used directly in Twig templates to iterate through post query results and
 * retrieve meta information about them.
 *
 * @api
 */
class PostQuery extends ArrayObject implements PostCollectionInterface, JsonSerializable {
	use AccessesPostsLazily;

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
	public    $found_posts = null;

	/**
	 * If the user passed an array, it is stored here.
	 *
	 * @var array
	 */
	protected $userQuery;

	/**
	 * The internal WP_Query instance that this object is wrapping.
	 *
	 * @var \WP_Query
	 */
	protected $wp_query = null;

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
	 * @todo update these docs
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
	public function __construct( $args = null ) {
		// @todo remove these if/else clauses completely and deal directly w/ WP_Query
		if (is_array($args)) {
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
		} else {
			$args = ['query' => $args];
		}

		if ($args['query'] instanceof WP_Query) {
			// @todo this is the new happy path
			$this->wp_query = $args['query'];
			$this->found_posts = $this->wp_query->found_posts;

			$posts = $this->wp_query->posts ?: [];

		} else {

			// @todo we can eventually (mostly) remove this path
			if ( $args['merge_default'] ) {
				global $wp_query;

				// Merge query arguments with default query.
				$args['query'] = wp_parse_args( $args['query'], $wp_query->query_vars );
			}

			// NOTE: instead of doing this here, PostFactory should know whether to instantiate a PostQuery or not.
			// So if we're at this point we already know we want a QueryIterator!
			// @todo pass a WP_Query instance directly (we should get one from PostFactory)
			$this->userQuery     = $args['query'];
			$this->queryIterator = PostGetter::query_posts( $args['query'], $args['post_class'] );

			if ( $this->queryIterator instanceof QueryIterator ) {
				$this->found_posts = $this->queryIterator->found_posts();
			}

			// @todo if we already have a WP_Query instance, we can just get its posts directly.
			$posts = $this->queryIterator->get_posts();
		}

		parent::__construct( $posts, 0, PostsIterator::class );
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
			$this->pagination = $this->queryIterator->get_pagination($prefs, $this->userQuery);
		} elseif ( !$this->pagination && $this->wp_query instanceof WP_Query ) {
			$this->pagination = new Pagination($prefs, $this->wp_query);
		}

		return $this->pagination;
	}

	/**
	 * Override data printed by var_dump() and similar. Realizes the collection before
	 * returning. Due to a PHP bug, this only works in PHP >= 7.4.
	 *
	 * @see https://bugs.php.net/bug.php?id=69264
	 * @internal
	 */
	public function __debugInfo() {
		return [
			'info' => sprintf( '
********************************************************************************

    This output is generated by %s().

    The properties you see here are not actual properties, but only debug
    output. If you want to access the actual instances of Timber\Posts, loop
		over the collection or get all posts through $query->to_array().

		More info: https://timber.github.io/docs/v2/guides/posts/#debugging-post-collections

********************************************************************************',
				__METHOD__
			),
			'posts'       => $this->getArrayCopy(),
			'wp_query'    => $this->wp_query,
			'found_posts' => $this->found_posts,
			'pagination'  => $this->pagination,
			'factory'     => $this->factory,
			'iterator'    => $this->getIterator(),
		];
	}

	/**
	 * Returns realized (eagerly instantiated) Timber\Post data to serialize to JSON.
	 *
	 * @internal
	 */
	public function jsonSerialize() {
		return $this->getArrayCopy();
	}

}
