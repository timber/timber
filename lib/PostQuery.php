<?php

namespace Timber;

use ArrayObject;
use JsonSerializable;
use WP_Post;
use WP_Query;

use Timber\Factory\PostFactory;

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
	protected $_query = null;

	/**
	 * @var PostCollection|QueryIterator
	 */
	protected $queryIterator;

	protected $pagination = null;

	/**
	 * Whether Timber\Post instances have been lazily instantiated.
	 *
	 * @var bool
	 */
	private $realized = false;

	/**
	 * PostFactory instance used internally to instantiate Posts.
	 *
	 * @var \Timber\Factory\PostFactory
	 */
	private $factory;

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

		if ($args['query'] instanceof WP_Query) {
			// @todo this is the new happy path
			$this->_query = $args['query'];
			$this->found_posts = $this->_query->found_posts;

			$posts = $this->_query->posts ?: [];

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
		} elseif ( !$this->pagination && $this->_query instanceof WP_Query ) {
			$this->pagination = new Pagination($prefs, $this->_query);
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
			'__posts'     => $this->getArrayCopy(),
			'_query'      => $this->_query,
			'found_posts' => $this->found_posts,
			'pagination'  => $this->pagination,
			'factory'     => $this->factory,
			'iterator'    => $this->getIterator(),
		];
	}

	/**
	 * @internal
	 */
	public function getArrayCopy() {
		// Force eager instantiation of Timber\Posts before returning them all in an array.
		$this->realize();
		return parent::getArrayCopy();
	}

	/**
	 * For better performance, PostQuery does not instantiate `Timber\Post` objects
	 * at query time. It instantiates each `Timber\Post` only as needed, i.e. while
	 * iterating or for direct array access (`$coll[$i]`). Since specific `Timber\Post`
	 * implementations may have expensive `::setup()` operations, this is usually
	 * what you want, but not always. For example, you may want to force eager
	 * instantiation to front-load a collection to be cached. To eagerly instantiate
	 * a lazy collection of objects is to "realize" that collection.
	 *
	 * @api
	 * @example
	 * ```php
	 * $lazy_posts = \Timber\Helper::transient('my_posts', function() {
	 *   return \Timber\Timber::get_posts([
	 * 		 'post_type' => 'some_post_type',
	 *   ]);
	 * }, HOUR_IN_SECONDS);
	 *
	 * foreach ($lazy_posts as $post) {
	 *   // This will incur the performance cost of Post::setup()
	 * }
	 *
	 * // Contrast with:
	 *
	 * $eager_posts = \Timber\Helper::transient('my_posts', function() {
	 *   $query = \Timber\Timber::get_posts([
	 * 		 'post_type' => 'some_post_type',
	 *   ]);
	 *   // Incur Post::setup() cost up front.
	 *   return $query->realize();
	 * }, HOUR_IN_SECONDS);
	 *
	 * foreach ($eager_posts as $post) {
	 *   // No additional overhead here.
	 * }
	 * ```
	 * @return \Timber\PostQuery the realized PostQuery
	 */
	public function realize() : self {
		if (!$this->realized) {
			// offsetGet() is where lazy instantiation actually happens.
			// Since arbitrary array index access may have happened previously,
			// leverage that to ensure each Post is instantiated exactly once.
			// We call parent::getArrayCopy() to avoid infinite mutual recursion.
			foreach (array_keys(parent::getArrayCopy()) as $k) {
				$this->offsetGet($k);
			}
			$this->realized = true;
		}

		return $this;
	}

	/**
	 * @api
	 * @return array
	 */
	public function to_array() : array {
		return $this->getArrayCopy();
	}

	/**
	 * @todo should we deprecate this method in favor of to_array()?
	 * @api
	 * @return array
	 */
	public function get_posts() : array {
		return $this->getArrayCopy();
	}

	/**
	 * Returns realized (eagerly instantiated) Timber\Post data to serialize to JSON.
	 *
	 * @internal
	 */
	public function jsonSerialize() {
		return $this->getArrayCopy();
	}


	/**
	 * Lazily instantiates Timber\Post instances from WP_Post objects.
	 *
	 * @internal
	 */
	public function offsetGet($offset) {
		$post = parent::offsetGet($offset);
		if ($post instanceof WP_Post) {
			$post = $this->factory()->from($post);
			$this->offsetSet($offset, $post);
		}

		return $post;
	}

	/**
	 * @internal
	 */
	private function factory() : PostFactory {
		if (!$this->factory) {
			$this->factory = new PostFactory();
		}

		return $this->factory;
	}
}
