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
    public function from($params, array $options = [])
    {
        $options = \wp_parse_args($options, [
            'merge' => true,
        ]);

        // Single term by ID.
        if (\is_int($params) || (\is_string($params) && \is_numeric($params))) {
            return $this->from_id((int) $params);
        }

        // Non-query object (WP_Term, CoreInterface).
        if (\is_object($params) && !($params instanceof WP_Term_Query)) {
            return $this->from_term_object($params);
        }

        // Flat list of individual term IDs or objects.
        if ($this->is_numeric_array($params) && !$this->is_array_of_strings($params)) {
            return \array_map([$this, 'from'], $params);
        }

        // All remaining cases (taxonomy name/s, WP_Term_Query, query args array) resolve
        // to a list of terms that may be grouped by taxonomy.
        [$result, $queryParams] = $this->resolve_to_term_list($params);

        return $this->maybe_group_by_taxonomy($result, $queryParams, $options);
    }

    /**
     * Resolves any list-producing input into a [terms, queryParams] pair.
     *
     * @param mixed $params The input to resolve.
     * @return array The [terms, queryParams] pair.
     */
    private function resolve_to_term_list($params): array
    {
        // Single taxonomy name string.
        if (\is_string($params)) {
            return [
                $this->from_taxonomy_names([$params]),
                [
                    'taxonomy' => [$params],
                ],
            ];
        }

        if ($params instanceof WP_Term_Query) {
            return [$this->from_wp_term_query($params), $params];
        }

        // Numeric array of taxonomy name strings, e.g. ['category', 'post_tag'].
        if ($this->is_array_of_strings($params)) {
            return [
                $this->from_taxonomy_names($params),
                [
                    'taxonomy' => $params,
                ],
            ];
        }

        // Associative array of WP_Term_Query args.
        $query = new WP_Term_Query($this->filter_query_params($params));
        return [$this->from_wp_term_query($query), $params];
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
            $obj::class
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

        $class ??= Term::class;

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

        return \array_map(fn ($taxonomy) => $corrections[$taxonomy] ?? $taxonomy, $taxonomies);
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

    /**
     * Groups results by taxonomy if merge is false and multiple taxonomies are present.
     *
     * @internal
     * @param array $results The query results (term objects).
     * @param mixed $params The original query parameters.
     * @param array $options The options array containing the merge setting.
     * @return array The results, either as-is or grouped by taxonomy.
     */
    protected function maybe_group_by_taxonomy($results, $params, array $options): mixed
    {
        if ($options['merge'] || !\is_array($results)) {
            return $results;
        }

        // Group results by taxonomy
        $grouped = [];
        foreach ($results as $term) {
            if ($term instanceof Term) {
                $grouped[$term->taxonomy][] = $term;
            }
        }

        // For WP_Term_Query objects, group by taxonomy without ordering
        if ($params instanceof WP_Term_Query) {
            return $grouped;
        }

        // Only group if we have multiple taxonomies
        if (\count($grouped) <= 1) {
            return $results;
        }

        // Sort by taxonomy order if explicitly specified in params
        if (\is_array($params) && isset($params['taxonomy']) && \is_array($params['taxonomy'])) {
            $ordered = [];
            foreach ($params['taxonomy'] as $taxonomy) {
                if (isset($grouped[$taxonomy])) {
                    $ordered[$taxonomy] = $grouped[$taxonomy];
                }
            }
            return $ordered;
        }

        // For simple arrays (term IDs, WP_Term objects, etc.), return flat
        return $results;
    }
}
