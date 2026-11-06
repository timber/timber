<?php

namespace Timber;

use WP_Post_Type;

/**
 * Class PostType
 *
 * Represents a registered post type, giving you access to everything that was passed to
 * `register_post_type()` – most notably the `labels` – as well as the taxonomies that are
 * registered for it.
 *
 * @api
 * @since 1.0.4
 * @extends RegisteredEntity<WP_Post_Type>
 * @example
 * ```php
 * $context['post_type'] = Timber::get_post_type('recipe');
 *
 * Timber::render('archive.twig', $context);
 * ```
 * ```twig
 * <h1>{{ post_type.labels.name }}</h1>
 *
 * {% for taxonomy in post_type.taxonomies %}
 *     <h2>{{ taxonomy.title }}</h2>
 * {% endfor %}
 * ```
 */
class PostType extends RegisteredEntity
{
    public static $representation = 'post_type';

    /**
     * @todo Deprecate the public constructor in favor of `Timber::get_post_type()`.
     *
     * @param string|WP_Post_Type|null $post_type The name of a post type or the vanilla WordPress
     *                                            post type object. Names that aren’t registered are
     *                                            allowed. `null` is used internally by `build()`.
     */
    public function __construct($post_type = null)
    {
        if (null === $post_type) {
            return;
        }

        $wp_post_type = $post_type instanceof WP_Post_Type ? $post_type : \get_post_type_object($post_type);

        if ($wp_post_type) {
            $this->init($wp_post_type);
        } else {
            $this->name = $post_type;
        }
    }

    /**
     * Gets the taxonomies that are registered for this post type.
     *
     * @api
     * @example
     * ```twig
     * {% for taxonomy in recipe.taxonomies %}
     *     <h2>{{ taxonomy.title }}</h2>
     * {% endfor %}
     * ```
     *
     * @return Taxonomy[] The taxonomies, keyed by taxonomy name.
     */
    public function taxonomies(): array
    {
        return Timber::get_taxonomies([
            'post_type' => $this->name,
        ]);
    }

    protected function edit_capability(): string
    {
        return $this->cap->edit_posts ?? 'do_not_allow';
    }

    protected function edit_path(): string
    {
        return match ($this->name) {
            'post' => 'edit.php',
            'attachment' => 'upload.php',
            default => 'edit.php?post_type=' . $this->name,
        };
    }
}
