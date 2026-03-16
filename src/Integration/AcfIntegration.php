<?php

/**
 * Integration with Advanced Custom Fields (ACF)
 *
 * @package Timber
 */

namespace Timber\Integration;

use ACF;
use acf_field;
use DateTimeImmutable;
use Timber\Post;
use Timber\Term;
use Timber\Timber;
use Timber\User;

/**
 * Class used to handle integration with Advanced Custom Fields
 */
class AcfIntegration implements IntegrationInterface
{
    public function should_init(): bool
    {
        return \class_exists(ACF::class);
    }

    public function init(): void
    {
        \add_filter('timber/post/pre_meta', [self::class, 'post_get_meta_field'], 10, 5);
        \add_filter('timber/post/meta_object_field', [self::class, 'post_meta_object'], 10, 3);
        \add_filter('timber/term/pre_meta', [self::class, 'term_get_meta_field'], 10, 5);
        \add_filter('timber/user/pre_meta', [self::class, 'user_get_meta_field'], 10, 5);

        /**
         * Allowed a user to set a meta value
         *
         * @deprecated 2.0.0 with no replacement
         */
        \add_filter('timber/term/meta/set', [self::class, 'term_set_meta'], 10, 4);
    }

    /**
     * Gets meta value for a post through ACF’s API.
     *
     * @param string       $value      The field value. Default null.
     * @param int          $post_id    The post ID.
     * @param string       $field_name The name of the meta field to get the value for.
     * @param Post $post       The post object.
     * @param array        $args       An array of arguments.
     * @return mixed|false
     */
    public static function post_get_meta_field($value, $post_id, $field_name, $post, $args)
    {
        return self::get_meta($value, $post_id, $field_name, $args);
    }

    public static function post_meta_object($value, $post_id, $field_name)
    {
        return \get_field_object($field_name, $post_id);
    }

    /**
     * Gets meta value for a term through ACF’s API.
     *
     * @param string       $value      The field value. Default null.
     * @param int          $term_id    The term ID.
     * @param string       $field_name The name of the meta field to get the value for.
     * @param Term $term       The term object.
     * @param array        $args       An array of arguments.
     * @return mixed|false
     */
    public static function term_get_meta_field($value, $term_id, $field_name, $term, $args)
    {
        return self::get_meta($value, $term->taxonomy . '_' . $term_id, $field_name, $args);
    }

    /**
     * @deprecated 2.0.0, with no replacement
     *
     * @return mixed
     */
    public static function term_set_meta($value, $field, $term_id, $term)
    {
        $searcher = $term->taxonomy . '_' . $term->ID;
        \update_field($field, $value, $searcher);
        return $value;
    }

    /**
     * Gets meta value for a user through ACF’s API.
     *
     * @param string       $value      The field value. Default null.
     * @param int          $user_id    The user ID.
     * @param string       $field_name The name of the meta field to get the value for.
     * @param User $user       The user object.
     * @param array        $args       An array of arguments.
     * @return mixed|false
     */
    public static function user_get_meta_field($value, $user_id, $field_name, $user, $args)
    {
        return self::get_meta($value, 'user_' . $user_id, $field_name, $args);
    }

    /**
     * Transform ACF file field
     *
     * @param string $value
     * @param int    $id
     * @param array  $field
     */
    public static function transform_file($value, $id, $field)
    {
        if (empty($value)) {
            return false;
        }
        return Timber::get_attachment($value);
    }

    /**
     * Transform ACF image field
     *
     * @param string $value
     * @param int    $id
     * @param array  $field
     */
    public static function transform_image($value, $id, $field)
    {
        if (empty($value)) {
            return false;
        }
        return Timber::get_image($value);
    }

    /**
     * Transform ACF gallery field
     *
     * @param array $value
     * @param int   $id
     * @param array $field
     */
    public static function transform_gallery($value, $id, $field)
    {
        if (empty($value)) {
            return false;
        }
        return Timber::get_posts($value);
    }

    /**
     * Transform ACF date picker field
     *
     * @param string $value
     * @param int    $id
     * @param array  $field
     */
    public static function transform_date_picker($value, $id, $field)
    {
        if (!$value) {
            return $value;
        }
        return new DateTimeImmutable(\acf_format_date($value, 'Y-m-d H:i:s'), \wp_timezone());
    }

    /**
     * Transform ACF post object field
     *
     * @param string $value
     * @param int    $id
     * @param array  $field
     */
    public static function transform_post_object($value, $id, $field)
    {
        if (empty($value)) {
            return false;
        }
        if (!$field['multiple']) {
            return Timber::get_post($value);
        }
        return Timber::get_posts($value);
    }

    /**
     * Transform ACF relationship field
     *
     * @param string $value
     * @param int    $id
     * @param array  $field
     */
    public static function transform_relationship($value, $id, $field)
    {
        if (empty($value)) {
            return false;
        }
        return Timber::get_posts($value);
    }

    /**
     * Transform ACF taxonomy field
     *
     * @param string $value
     * @param int    $id
     * @param array  $field
     */
    public static function transform_taxonomy($value, $id, $field)
    {
        if (empty($value)) {
            return false;
        }
        if ($field['field_type'] === 'select' || $field['field_type'] === 'radio') {
            return Timber::get_term((int) $value);
        }
        return Timber::get_terms((array) $value);
    }

    /**
     * Transform ACF user field
     *
     * @param string $value
     * @param int    $id
     * @param array  $field
     */
    public static function transform_user($value, $id, $field)
    {
        if (empty($value)) {
            return false;
        }
        if (!$field['multiple']) {
            return Timber::get_user((int) $value);
        }
        return Timber::get_users((array) $value);
    }

    /**
     * Gets a single ACF option field with field transformations applied.
     *
     * Retrieves a field stored on an ACF option page and automatically applies Timber's field
     * transformations, converting raw values into Timber objects (e.g. Image, Post, Term).
     *
     * Example usage:
     *
     * ```php
     * $gallery = Timber\Integration\AcfIntegration::get_option('my_gallery_field');
     * ```
     * @api
     * @since 2.4.0
     * @param string $field_name The name of the option field to retrieve.
     * @return mixed The transformed field value.
     */
    public static function get_option(string $field_name): mixed
    {
        return self::with_timber_transforms(static fn() => \get_field($field_name, 'options', true));
    }

    /**
     * Gets all ACF option fields with field transformations applied.
     *
     * Retrieves all fields stored on ACF option pages and automatically applies Timber's field
     * transformations, converting raw values into Timber objects (e.g. Image, Post, Term).
     *
     * Example usage:
     *
     * ```php
     * $options = Timber\Integration\AcfIntegration::get_options();
     * ```
     * @api
     * @since 2.4.0
     * @return array An associative array of all transformed option fields.
     */
    public static function get_options(): array
    {
        return self::with_timber_transforms(static fn() => \get_fields('options') ?: []);
    }

    /**
     * Gets meta value through ACF's API.
     *
     * @param string     $value
     * @param int|string $id
     * @param string     $field_name
     * @param array      $args
     * @return mixed|false
     */
    private static function get_meta($value, $id, $field_name, $args)
    {
        $args = \wp_parse_args($args, [
            'format_value' => true,
            'transform_value' => false,
        ]);

        if (!$args['transform_value']) {
            return \get_field($field_name, $id, $args['format_value']);
        }

        return self::with_timber_transforms(static fn() => \get_field($field_name, $id, true));
    }

    /**
     * Temporarily replaces ACF's format_value filters with Timber's transform filters, executes a
     * callback, then restores the original ACF filters.
     *
     * We use acf_get_field_type() instead of acf()->fields->get_field_type(), because of some
     * function stub issues in the php-stubs/acf-pro-stubs package.
     *
     * @see https://github.com/timber/timber/pull/2630
     *
     * @param callable $callback The callback to execute with Timber's transforms active.
     * @return mixed The result of the callback.
     */
    private static function with_timber_transforms(callable $callback): mixed
    {
        $field_types = \array_filter([
            'file' => \acf_get_field_type('file'),
            'image' => \acf_get_field_type('image'),
            'gallery' => \acf_get_field_type('gallery'),
            'date_picker' => \acf_get_field_type('date_picker'),
            'date_time_picker' => \acf_get_field_type('date_time_picker'),
            'post_object' => \acf_get_field_type('post_object'),
            'relationship' => \acf_get_field_type('relationship'),
            'taxonomy' => \acf_get_field_type('taxonomy'),
            'user' => \acf_get_field_type('user'),
        ], static fn($field_type): bool => $field_type instanceof acf_field);

        $timber_transforms = [
            'file' => [self::class, 'transform_file'],
            'image' => [self::class, 'transform_image'],
            'gallery' => [self::class, 'transform_gallery'],
            'date_picker' => [self::class, 'transform_date_picker'],
            'date_time_picker' => [self::class, 'transform_date_picker'],
            'post_object' => [self::class, 'transform_post_object'],
            'relationship' => [self::class, 'transform_relationship'],
            'taxonomy' => [self::class, 'transform_taxonomy'],
            'user' => [self::class, 'transform_user'],
        ];

        // Remove ACF's format_value filters for known field types (only if the field class exists).
        foreach ($field_types as $type => $field_type) {
            \remove_filter("acf/format_value/type={$type}", [$field_type, 'format_value']);
        }

        // Always add Timber's transform filters, even for field types without a registered ACF
        // field class (e.g. gallery in ACF Free where it is a PRO-only field type).
        foreach ($timber_transforms as $type => $callback_fn) {
            \add_filter("acf/format_value/type={$type}", $callback_fn, 10, 3);
        }

        $result = $callback();

        // Remove Timber's transform filters and restore ACF's format_value filters.
        foreach ($timber_transforms as $type => $callback_fn) {
            \remove_filter("acf/format_value/type={$type}", $callback_fn);
        }
        foreach ($field_types as $type => $field_type) {
            \add_filter("acf/format_value/type={$type}", [$field_type, 'format_value'], 10, 3);
        }

        return $result;
    }
}
