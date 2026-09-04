<?php

namespace Timber\Factory;

use InvalidArgumentException;
use Timber\CoreInterface;
use Timber\Taxonomy;
use WP_Taxonomy;

/**
 * Internal API class for instantiating Taxonomies
 */
class TaxonomyFactory
{
    /**
     * @param mixed $params A taxonomy name, an array of taxonomy names, an array of arguments for
     *                      `get_taxonomies()`, a `WP_Taxonomy` object or a `Timber\Taxonomy`.
     *
     * @return Taxonomy|Taxonomy[]|null
     */
    public function from($params)
    {
        if (\is_object($params)) {
            return $this->from_taxonomy_object($params);
        }

        if (\is_string($params)) {
            return $this->from_name($params);
        }

        if (!\is_array($params)) {
            throw new InvalidArgumentException(\sprintf(
                'Expected a taxonomy name, an array or an instance of Timber\CoreInterface or WP_Taxonomy, got %s',
                \get_debug_type($params)
            ));
        }

        // A flat list of taxonomy names.
        if (!empty($params) && \array_is_list($params) && $this->is_array_of_strings($params)) {
            return $this->from_names($params);
        }

        return $this->from_query_args($params);
    }

    protected function from_name(string $name): ?Taxonomy
    {
        $wp_taxonomy = \get_taxonomy($name);

        if (!$wp_taxonomy) {
            return null;
        }

        return $this->build($wp_taxonomy);
    }

    /**
     * @param string[] $names
     * @return Taxonomy[] Keyed by taxonomy name. Names that aren’t registered are skipped.
     */
    protected function from_names(array $names): array
    {
        $taxonomies = [];

        foreach ($names as $name) {
            $taxonomy = $this->from_name($name);

            if ($taxonomy) {
                $taxonomies[$name] = $taxonomy;
            }
        }

        return $taxonomies;
    }

    /**
     * @return Taxonomy[] Keyed by taxonomy name.
     */
    protected function from_query_args(array $args): array
    {
        // WP’s own `object_type` argument requires an exact match of the registered post types,
        // so the `post_type` alias is resolved separately.
        $post_types = $args['post_type'] ?? null;
        unset($args['post_type']);

        $names = \get_taxonomies($args);

        if (null !== $post_types) {
            $names = \array_intersect($names, $this->taxonomy_names_for_post_types((array) $post_types));
        }

        return $this->from_names(\array_values($names));
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

    protected function from_taxonomy_object(object $obj): CoreInterface
    {
        if ($obj instanceof CoreInterface) {
            // We already have a Timber Core object of some kind.
            return $obj;
        }

        if ($obj instanceof WP_Taxonomy) {
            return $this->build($obj);
        }

        throw new InvalidArgumentException(\sprintf(
            'Expected an instance of Timber\CoreInterface or WP_Taxonomy, got %s',
            $obj::class
        ));
    }

    protected function get_taxonomy_class(WP_Taxonomy $taxonomy): string
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
        $map = \apply_filters('timber/taxonomy/classmap', []);

        $class = $map[$taxonomy->name] ?? null;

        if (\is_callable($class)) {
            $class = $class($taxonomy);
        }

        $class ??= Taxonomy::class;

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

    protected function build(WP_Taxonomy $taxonomy): CoreInterface
    {
        $class = $this->get_taxonomy_class($taxonomy);

        return $class::build($taxonomy);
    }

    protected function is_array_of_strings($arr)
    {
        if (!\is_array($arr)) {
            return false;
        }
        foreach ($arr as $v) {
            if (!\is_string($v)) {
                return false;
            }
        }
        return true;
    }
}
