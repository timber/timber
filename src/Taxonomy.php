<?php

namespace Timber;

use WP_Taxonomy;

/**
 * Class Taxonomy
 *
 * Represents a registered taxonomy, giving you access to everything that was passed to
 * `register_taxonomy()` – most notably the `labels` – as well as the terms that belong to it.
 *
 * @api
 * @since 2.6.0
 * @extends RegisteredEntity<WP_Taxonomy>
 * @example
 * ```php
 * $context['genre'] = Timber::get_taxonomy('genre');
 *
 * Timber::render('archive.twig', $context);
 * ```
 * ```twig
 * <h1>{{ genre.labels.all_items }}</h1>
 *
 * <ul>
 *     {% for term in genre.terms %}
 *         <li><a href="{{ term.link }}">{{ term.title }}</a></li>
 *     {% endfor %}
 * </ul>
 * ```
 */
class Taxonomy extends RegisteredEntity
{
    public static $representation = 'taxonomy';

    protected function edit_capability(): string
    {
        return $this->cap->manage_terms;
    }

    protected function edit_path(): string
    {
        return 'edit-tags.php?taxonomy=' . $this->name;
    }

    /**
     * Gets the default term of the taxonomy.
     *
     * A default term can be defined through the `default_term` argument of
     * `register_taxonomy()`. It is assigned to a post whenever no other term of this taxonomy is.
     *
     * @api
     * @example
     * ```twig
     * {% if genre.default_term %}
     *     <a href="{{ genre.default_term.link }}">{{ genre.default_term.title }}</a>
     * {% endif %}
     * ```
     *
     * @return Term|null The default term or `null` if the taxonomy doesn’t have one.
     */
    public function default_term(): ?Term
    {
        $term_id = \get_option('default_term_' . $this->name);

        if (!$term_id) {
            return null;
        }

        return Timber::get_term((int) $term_id);
    }

    /**
     * Gets the terms that belong to this taxonomy.
     *
     * Terms are only queried when this method is called, so getting a taxonomy is cheap even when
     * you never display its terms.
     *
     * @api
     * @example
     * ```twig
     * {% for term in genre.terms %}
     *     <a href="{{ term.link }}">{{ term.title }}</a>
     * {% endfor %}
     *
     * {# Pass in WP_Term_Query arguments to refine the result. #}
     * {% for term in genre.terms({ hide_empty: false, orderby: 'count' }) %}
     *     {{ term.title }}
     * {% endfor %}
     * ```
     *
     * @param array $query_args Optional. Arguments for `WP_Term_Query`. The `taxonomy` argument is
     *                          always set to this taxonomy. Default empty array.
     * @param array $options    Optional. Options for `Timber::get_terms()`. Default empty array.
     *
     * @return iterable An iterable of `Timber\Term` objects.
     */
    public function terms(array $query_args = [], array $options = []): iterable
    {
        return Timber::get_terms(\array_merge($query_args, [
            'taxonomy' => $this->name,
        ]), $options);
    }

    /**
     * Gets the post types this taxonomy is registered for.
     *
     * @api
     * @example
     * ```twig
     * {% for post_type in genre.post_types %}
     *     {{ post_type.labels.name }}
     * {% endfor %}
     * ```
     *
     * @return PostType[]
     */
    public function post_types(): array
    {
        return \array_map(
            static fn ($post_type) => new PostType($post_type),
            \array_values(\array_filter($this->wp_object?->object_type ?? [], post_type_exists(...)))
        );
    }
}
