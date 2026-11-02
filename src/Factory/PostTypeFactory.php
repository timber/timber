<?php

namespace Timber\Factory;

use Timber\PostType;
use WP_Post_Type;

/**
 * Internal API class for instantiating post types
 *
 * Accepts a post type name, an array of post type names, an array of arguments for
 * `get_post_types()`, a `WP_Post_Type` object or a `Timber\PostType`.
 */
class PostTypeFactory extends RegisteredEntityFactory
{
    protected function entity_class(): string
    {
        return PostType::class;
    }

    protected function wp_object_class(): string
    {
        return WP_Post_Type::class;
    }

    protected function get_wp_object(string $name): ?object
    {
        return \get_post_type_object($name);
    }

    /**
     * @return string[]
     */
    protected function query_names(array $args): array
    {
        return \get_post_types($args);
    }

    protected function apply_classmap_filter(): array
    {
        /**
         * Filters the class(es) used for different post types.
         *
         * @since 2.6.0
         * @example
         * ```
         * add_filter( 'timber/post_type/classmap', function( $classmap ) {
         *     $custom_classmap = [
         *         'recipe' => RecipePostType::class,
         *     ];
         *
         *     return array_merge( $classmap, $custom_classmap );
         * } );
         * ```
         *
         * @param array $classmap The post type class(es) to use. An associative array where the
         *                        key is the post type name and the value the name of the class to
         *                        use for this post type or a callback that determines the class
         *                        to use.
         */
        return \apply_filters('timber/post_type/classmap', []);
    }

    protected function apply_class_filter(string $class, object $post_type): string
    {
        /**
         * Filters the post type class based on your custom criteria.
         *
         * @since 2.6.0
         * @example
         * ```
         * add_filter( 'timber/post_type/class', function( $class, $post_type ) {
         *     if ( $post_type->hierarchical ) {
         *         return MyHierarchicalPostType::class;
         *     }
         *
         *     return $class;
         * }, 10, 2 );
         * ```
         *
         * @param string       $class     The class to use.
         * @param WP_Post_Type $post_type The post type object.
         */
        return \apply_filters('timber/post_type/class', $class, $post_type);
    }
}
