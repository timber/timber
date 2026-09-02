<?php

namespace Timber;

use Stringable;
use WP_Taxonomy;

/**
 * Class Taxonomy
 *
 * Represents a registered taxonomy, giving you access to everything that was passed to
 * `register_taxonomy()` – most notably the `labels` – as well as the terms that belong to it.
 *
 * @api
 * @since 2.6.0
 * @phpstan-consistent-constructor
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
class Taxonomy extends Core implements CoreInterface, Stringable
{
    /**
     * The underlying WordPress Core object.
     *
     * @var WP_Taxonomy|null
     */
    protected ?WP_Taxonomy $wp_object = null;

    public static $representation = 'taxonomy';

    /**
     * @api
     * @var string The name the taxonomy was registered with (ex: `post_tag` or `genre`).
     */
    public $name;

    /**
     * @api
     * @var object The taxonomy’s labels, as returned by `get_taxonomy_labels()`.
     */
    public $labels;

    /**
     * @api
     * @var object The taxonomy’s capabilities, as returned by `register_taxonomy()`.
     */
    public $cap;

    /**
     * Memoized result of an unfiltered `terms()` call.
     *
     * @var iterable|null
     */
    private ?iterable $terms_cache = null;

    /**
     * @internal
     */
    protected function __construct()
    {
    }

    /**
     * @internal
     *
     * @param WP_Taxonomy $wp_taxonomy The vanilla WordPress taxonomy object to build from.
     * @return static
     */
    public static function build(WP_Taxonomy $wp_taxonomy): static
    {
        $taxonomy = new static();
        $taxonomy->init($wp_taxonomy);

        return $taxonomy;
    }

    /**
     * @internal
     */
    protected function init(WP_Taxonomy $wp_taxonomy): void
    {
        $this->wp_object = $wp_taxonomy;
        $this->import($wp_taxonomy);
    }

    /**
     * The string the taxonomy will render as by default.
     *
     * @api
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Gets the underlying WordPress Core object.
     *
     * @api
     * @return WP_Taxonomy|null
     */
    public function wp_object(): ?WP_Taxonomy
    {
        return $this->wp_object;
    }

    /**
     * Gets the human-readable name of the taxonomy.
     *
     * Use this instead of `name`, which holds the name the taxonomy was registered with
     * (ex: `post_tag`), and not the label you defined for it (ex: `Tags`).
     *
     * @api
     * @example
     * ```twig
     * <h1>{{ genre.title }}</h1>
     * ```
     *
     * @return string
     */
    public function title(): string
    {
        return $this->labels->name ?? $this->name;
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
     * Checks whether the current user can manage the terms of this taxonomy.
     *
     * @api
     * @example
     * ```twig
     * {% if genre.can_edit %}
     *     <a href="{{ genre.edit_link }}">Manage genres</a>
     * {% endif %}
     * ```
     *
     * @return bool
     */
    public function can_edit(): bool
    {
        return \current_user_can($this->cap->manage_terms);
    }

    /**
     * Gets the link to the term overview of this taxonomy in the WordPress admin.
     *
     * @api
     * @example
     * ```twig
     * {% if genre.can_edit %}
     *     <a href="{{ genre.edit_link }}">Manage genres</a>
     * {% endif %}
     * ```
     *
     * @return string|null The edit URL or `null` if the current user can’t manage the terms of
     *                     this taxonomy.
     */
    public function edit_link(): ?string
    {
        if (!$this->can_edit()) {
            return null;
        }

        return \admin_url('edit-tags.php?taxonomy=' . $this->name);
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
        if (empty($query_args) && empty($options) && $this->terms_cache !== null) {
            return $this->terms_cache;
        }

        $terms = Timber::get_terms(\array_merge($query_args, [
            'taxonomy' => $this->name,
        ]), $options);

        if (empty($query_args) && empty($options)) {
            $this->terms_cache = $terms;
        }

        return $terms;
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
            $this->wp_object?->object_type ?? []
        );
    }
}
