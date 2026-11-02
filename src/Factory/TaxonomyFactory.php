<?php

namespace Timber\Factory;

use Timber\Taxonomy;
use WP_Taxonomy;

/**
 * Internal API class for instantiating Taxonomies
 *
 * Accepts a taxonomy name, an array of taxonomy names, an array of arguments for
 * `get_taxonomies()`, a `WP_Taxonomy` object or a `Timber\Taxonomy`.
 */
class TaxonomyFactory extends RegisteredEntityFactory
{
    protected function entity_class(): string
    {
        return Taxonomy::class;
    }

    protected function wp_object_class(): string
    {
        return WP_Taxonomy::class;
    }

    protected function get_wp_object(string $name): ?object
    {
        return \get_taxonomy($name) ?: null;
    }

    /**
     * @return string[]
     */
    protected function query_names(array $args): array
    {
        // WP’s own `object_type` argument requires an exact match of the registered post types,
        // so the `post_type` alias is resolved separately.
        $post_types = $args['post_type'] ?? null;
        unset($args['post_type']);

        $names = \get_taxonomies($args);

        if (null !== $post_types) {
            $names = \array_intersect($names, $this->taxonomy_names_for_post_types((array) $post_types));
        }

        return $names;
    }

    /**
     * @param string[] $post_types
     * @return string[]
     */
    protected function taxonomy_names_for_post_types(array $post_types): array
    {
        $names = [];

        foreach ($post_types as $post_type) {
            $names = \array_merge($names, \get_object_taxonomies($post_type));
        }

        return \array_unique($names);
    }

    protected function apply_classmap_filter(): array
    {
        /**
         * Filters the class(es) used for different taxonomies.
         *
         * @since 2.6.0
         * @example
         * ```
         * add_filter( 'timber/taxonomy/classmap', function( $classmap ) {
         *     $custom_classmap = [
         *         'genre' => GenreTaxonomy::class,
         *     ];
         *
         *     return array_merge( $classmap, $custom_classmap );
         * } );
         * ```
         *
         * @param array $classmap The taxonomy class(es) to use. An associative array where the key
         *                        is the taxonomy name and the value the name of the class to use
         *                        for this taxonomy or a callback that determines the class to use.
         */
        return \apply_filters('timber/taxonomy/classmap', []);
    }

    protected function apply_class_filter(string $class, object $taxonomy): string
    {
        /**
         * Filters the taxonomy class based on your custom criteria.
         *
         * @since 2.6.0
         * @example
         * ```
         * add_filter( 'timber/taxonomy/class', function( $class, $taxonomy ) {
         *     if ( $taxonomy->hierarchical ) {
         *         return MyHierarchicalTaxonomy::class;
         *     }
         *
         *     return $class;
         * }, 10, 2 );
         * ```
         *
         * @param string      $class    The class to use.
         * @param WP_Taxonomy $taxonomy The taxonomy object.
         */
        return \apply_filters('timber/taxonomy/class', $class, $taxonomy);
    }
}
