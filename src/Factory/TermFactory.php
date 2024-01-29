<?php

namespace Timber\Factory;

use InvalidArgumentException;
use Timber\CoreInterface;
use Timber\Term;
use WP_Term;
use WP_Term_Query;

/**
 * Internal API class for instantiating Terms
 */
class TermFactory
{
    public function from($params)
    {
        if (\is_int($params) || \is_string($params) && \is_numeric($params)) {
            return $this->from_id((int) $params);
        }

        if (\is_string($params)) {
            return $this->from_taxonomy_names([$params]);
        }

        if ($params instanceof WP_Term_Query) {
            return $this->from_wp_term_query($params);
        }

        if (\is_object($params)) {
            return $this->from_term_object($params);
        }

        if ($this->is_numeric_array($params)) {
            if ($this->is_array_of_strings($params)) {
                return $this->from_taxonomy_names($params);
            }

            return \array_map([$this, 'from'], $params);
        }

        if (\is_array($params)) {
            return $this->from_wp_term_query(new WP_Term_Query(
                $this->filter_query_params($params)
            ));
        }

        return null;
    }

    protected function from_id(int $id): ?Term
    {
        $wp_term = \get_term($id);

        if (!$wp_term) {
            return null;
        }

        return $this->build($wp_term);
    }

    protected function from_wp_term_query(WP_Term_Query $query)
    {
        $terms = $query->get_terms();

        $fields = $query->query_vars['fields'];
        if ('all' === $fields || 'all_with_object_id' === $fields) {
            return \array_map([$this, 'build'], $terms);
        }

        return $terms;
    }

    protected function from_term_object(object $obj): CoreInterface
    {
        if ($obj instanceof CoreInterface) {
            // We already have a Timber Core object of some kind
            return $obj;
        }

        if ($obj instanceof WP_Term) {
            return $this->build($obj);
        }

        throw new InvalidArgumentException(\sprintf(
            'Expected an instance of Timber\CoreInterface or WP_Term, got %s',
            \get_class($obj)
        ));
    }

    protected function from_taxonomy_names(array $names)
    {
        return $this->from_wp_term_query(new WP_Term_Query(
            $this->filter_query_params([
                'taxonomy' => $names,
            ])
        ));
    }

    protected function get_term_class(WP_Term $term): string
    {
        /**
         * Filters the class(es) used for terms of different taxonomies.
         *
         * The default Term Class Map will contain class names mapped to the build-in post_tag and category taxonomies.
         *
         * @since 2.0.0
         * @example
         * ```
         * add_filter( 'timber/term/classmap', function( $classmap ) {
         *     $custom_classmap = [
         *         'expertise'   => ExpertiseTerm::class,
         *     ];
         *
         *     return array_merge( $classmap, $custom_classmap );
         * } );
         * ```
         *
         * @param array $classmap The term class(es) to use. An associative array where the key is
         *                        the taxonomy name and the value the name of the class to use for this
         *                        taxonomy or a callback that determines the class to use.
         */
        $map = \apply_filters('timber/term/classmap', [
            'post_tag' => Term::class,
            'category' => Term::class,
        ]);

        $class = $map[$term->taxonomy] ?? null;

        if (\is_callable($class)) {
            $class = $class($term);
        }

        $class = $class ?? Term::class;

        /**
         * Filters the term class based on your custom criteria.
         *
         * Maybe you want to set a custom class based upon a certain category?
         * This allows you to filter the PHP class, utilizing data from the WP_Term object.
         *
         * @since 2.0.0
         * @example
         * ```
         * add_filter( 'timber/term/class', function( $class, $term ) {
         *     if ( get_term_meta($term->term_id, 'is_special_category', true) ) {
         *         return MyCustomTermClass::class;
         *     }
         *
         *     return $class;
         * }, 10, 2 );
         * ```
         *
         * @param string $class The class to use.
         * @param WP_Term $term The term object.
         */
        $class = \apply_filters('timber/term/class', $class, $term);

        return $class;
    }

    protected function build(WP_Term $term): CoreInterface
    {
        $class = $this->get_term_class($term);

        return $class::build($term);
    }

    protected function correct_tax_key(array $params)
    {
        $corrections = [
            'taxonomies' => 'taxonomy',
            'taxs' => 'taxonomy',
            'tax' => 'taxonomy',
        ];

        foreach ($corrections as $mistake => $correction) {
            if (isset($params[$mistake])) {
                $params[$correction] = $params[$mistake];
            }
        }

        return $params;
    }

    protected function correct_taxonomies($tax): array
    {
        $taxonomies = \is_array($tax) ? $tax : [$tax];

        $corrections = [
            'categories' => 'category',
            'tags' => 'post_tag',
            'tag' => 'post_tag',
        ];

        return \array_map(function ($taxonomy) use ($corrections) {
            return $corrections[$taxonomy] ?? $taxonomy;
        }, $taxonomies);
    }

    protected function filter_query_params(array $params)
    {
        $params = $this->correct_tax_key($params);

        if (isset($params['taxonomy'])) {
            $params['taxonomy'] = $this->correct_taxonomies($params['taxonomy']);
        }

        $include = $params['term_id'] ?? null;
        if ($include) {
            $params['include'] = \is_array($include) ? $include : [$include];
        }

        return $params;
    }

    protected function is_numeric_array($arr)
    {
        if (!\is_array($arr)) {
            return false;
        }
        foreach (\array_keys($arr) as $k) {
            if (!\is_int($k)) {
                return false;
            }
        }
        return true;
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
