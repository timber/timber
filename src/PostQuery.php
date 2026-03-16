<?php

namespace Timber;

use ArrayObject;
use JsonSerializable;
use ReturnTypeWillChange;
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
class PostQuery extends ArrayObject implements PostCollectionInterface, JsonSerializable
{
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
    public $found_posts = null;

    /**
     * If the user passed an array, it is stored here.
     *
     * @var array
     */
    protected $userQuery;

    /**
     * The internal WP_Query instance that this object is wrapping.
     *
     * @var WP_Query
     */
    protected $wp_query = null;

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
     * // Get posts from default query.
     * global $wp_query;
     *
     * $posts = Timber::get_posts( $wp_query );
     *
     * // Using the WP_Query argument format.
     * $posts = Timber::get_posts( [
     *     'post_type'     => 'article',
     *     'category_name' => 'sports',
     * ] );
     *
     * // Passing a WP_Query instance.
     * $posts = Timber::get_posts( new WP_Query( [ 'post_type' => 'any' ) );
     * ```
     *
     * @param WP_Query $query The WP_Query object to wrap.
     */
    public function __construct(WP_Query $query)
    {
        $this->wp_query = $query;
        $this->found_posts = (int) $this->wp_query->found_posts;

        $posts = $this->wp_query->posts ?: [];

        parent::__construct($posts, 0, PostsIterator::class);
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
     * @return Pagination object
     */
    public function pagination($prefs = [])
    {
        if (!$this->pagination && $this->wp_query instanceof WP_Query) {
            $this->pagination = new Pagination($prefs, $this->wp_query);
        }

        return $this->pagination;
    }

    /**
     * Get terms from all posts in the query.
     *
     * Get terms associated with the posts in this collection, optionally filtered by taxonomy.
     * This is useful for creating taxonomy filters or displaying all terms used across a set of posts.
     *
     * @api
     * @since 2.1.0
     * @example
     * ```php
     * $posts = Timber::get_posts([
     *     'post_type' => 'projects',
     *     'category_name' => 'featured',
     * ]);
     *
     * // Get all terms from all taxonomies
     * $all_terms = $posts->terms();
     *
     * // Get terms from a specific taxonomy
     * $categories = $posts->terms('category');
     *
     * // Get terms from multiple taxonomies, grouped by taxonomy
     * $terms_by_tax = $posts->terms(['category', 'post_tag'], ['merge' => false]);
     * ```
     * ```twig
     * {# Display filter links for all categories used in the query #}
     * {% for category in posts.terms('category') %}
     *     <a href="{{ category.link }}">{{ category.name }}</a>
     * {% endfor %}
     *
     * {# Get terms grouped by taxonomy #}
     * {% set terms_by_taxonomy = posts.terms('all', {merge: false}) %}
     * {% for taxonomy, terms in terms_by_taxonomy %}
     *     <h3>{{ taxonomy }}</h3>
     *     <ul>
     *         {% for term in terms %}
     *             <li>{{ term.name }}</li>
     *         {% endfor %}
     *     </ul>
     * {% endfor %}
     * ```
     *
     * @param string|array $query_args Optional. A taxonomy slug (string), an array of
     *                                    taxonomy slugs, or an array of `WP_Term_Query`
     *                                    arguments. Default `[]` (all taxonomies).
     * @param array        $options      Optional. Configuration options. Default `[]`.
     *                                    - **merge**: (bool) Whether to merge terms from
     *                                      all taxonomies into a single array (`true`) or
     *                                      return them grouped by taxonomy (`false`).
     *                                      Default `true`.
     * @return iterable|array An iterable of `Timber\Term` objects, or an array of
     *                        iterables grouped by taxonomy name when `merge` is `false`.
     */
    public function terms($query_args = [], $options = [])
    {
        // Make it possible to use a taxonomy or an array of taxonomies as a shorthand.
        if (!\is_array($query_args) || isset($query_args[0])) {
            $query_args = [
                'taxonomy' => $query_args,
            ];
        }

        // Defaults.
        $query_args = \wp_parse_args($query_args, [
            'taxonomy' => 'all',
        ]);

        $options = \wp_parse_args($options, [
            'merge' => true,
        ]);

        $taxonomies = $query_args['taxonomy'];
        $merge = $options['merge'];

        // Get all post IDs from this collection.
        $post_ids = [];
        foreach ($this as $post) {
            $post_ids[] = $post->ID;
        }

        // If no posts, return empty result.
        if (empty($post_ids)) {
            return $merge ? [] : [];
        }

        // Determine which taxonomies to query.
        if (\in_array($taxonomies, ['all', 'any', ''])) {
            // Get all taxonomies for the post types in this query.
            $post_types = $this->wp_query->query_vars['post_type'] ?? 'post';
            if (\is_string($post_types)) {
                $post_types = [$post_types];
            }

            $taxonomies = [];
            foreach ($post_types as $post_type) {
                $taxonomies = \array_merge(
                    $taxonomies,
                    \get_object_taxonomies($post_type)
                );
            }
            $taxonomies = \array_unique($taxonomies);
        }

        if (!\is_array($taxonomies)) {
            $taxonomies = [$taxonomies];
        }

        // Build the query with all post IDs.
        $query = \array_merge($query_args, [
            'object_ids' => $post_ids,
            'taxonomy' => $taxonomies,
        ]);

        if (!$merge) {
            // Get results segmented out per taxonomy.
            $queries = $this->partition_tax_queries($query, $taxonomies);
            $termGroups = Timber::get_terms($queries);

            // Zip 'em up with the right keys.
            return \array_combine($taxonomies, $termGroups);
        }

        return Timber::get_terms($query, $options);
    }

    /**
     * Gets the original query used to get a collection of Timber posts.
     *
     * @since 2.0
     * @return WP_Query|null
     */
    public function query(): ?WP_Query
    {
        return $this->wp_query;
    }

    /**
     * Gets the original query used to get a collection of Timber posts.
     *
     * @deprecated 2.0.0, use PostQuery::query() instead.
     * @return WP_Query|null
     */
    public function get_query(): ?WP_Query
    {
        Helper::deprecated('Timber\PostQuery::get_query()', 'Timber\PostQuery::query()', '2.0.0');

        return $this->wp_query;
    }

    /**
     * Override data printed by var_dump() and similar. Realizes the collection before
     * returning. Due to a PHP bug, this only works in PHP >= 7.4.
     *
     * @see https://bugs.php.net/bug.php?id=69264
     * @internal
     */
    public function __debugInfo(): array
    {
        return [
            'info' => \sprintf(
                '
********************************************************************************

    This output is generated by %s().

    The properties you see here are not actual properties, but only debug
    output. If you want to access the actual instances of Timber\Posts, loop
        over the collection or get all posts through $query->to_array().

        More info: https://timber.github.io/docs/v2/guides/posts/#debugging-post-collections

********************************************************************************',
                __METHOD__
            ),
            'posts' => $this->getArrayCopy(),
            'wp_query' => $this->wp_query,
            'found_posts' => $this->found_posts,
            'pagination' => $this->pagination,
            'factory' => $this->factory,
            'iterator' => $this->getIterator(),
        ];
    }

    /**
     * Returns realized (eagerly instantiated) Timber\Post data to serialize to JSON.
     *
     * @internal
     */
    #[ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->getArrayCopy();
    }

    /**
     * Given a base query and a list of taxonomies, return a list of queries
     * each of which queries for one of the taxonomies.
     *
     * @internal
     * @param array $query      Base query arguments.
     * @param array $taxonomies List of taxonomy slugs.
     * @return array Array of query arguments, one per taxonomy.
     */
    private function partition_tax_queries(array $query, array $taxonomies): array
    {
        return \array_map(fn(string $tax): array => \array_merge($query, [
            'taxonomy' => [$tax],
        ]), $taxonomies);
    }
}
