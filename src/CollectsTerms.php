<?php

namespace Timber;

/**
 * Trait CollectsTerms
 *
 * Provides the ability to get terms from a collection of posts.
 *
 * @internal
 */
trait CollectsTerms
{
    /**
     * Get terms from all posts in the collection.
     *
     * Get terms associated with the posts in this collection, optionally filtered by taxonomy.
     * This is useful for creating taxonomy filters or displaying all terms used across a set of posts.
     *
     * @api
     * @since 2.4.0
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
     * {# Display filter links for all categories used in the collection #}
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

        // Get all post IDs from this collection.
        $post_ids = [];
        foreach ($this as $post) {
            $post_ids[] = $post->ID;
        }

        // If no posts, return empty result.
        if (empty($post_ids)) {
            return [];
        }

        // Determine which taxonomies to query.
        if (\in_array($taxonomies, ['all', 'any', ''])) {
            $post_types = $this->get_post_types_for_term_query();

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

        return Timber::get_terms($query, $options);
    }

    /**
     * Get the post types to use when determining which taxonomies to query.
     *
     * This method should be implemented by classes using this trait.
     *
     * @internal
     * @return array Array of post type slugs.
     */
    abstract protected function get_post_types_for_term_query();
}
