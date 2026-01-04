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
     * Gets meta value through ACF’s API.
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

        /**
         * We use acf()->fields->get_field_type() instead of acf_get_field_type(), because of some function stub issues
         * in the php-stubs/acf-pro-stubs package. The ACF plugin doesn't use the right parameter and return values for
         * some functions in the DocBlocks.
         *
         * @ticket https://github.com/timber/timber/pull/2630
         */
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
        ], static fn ($field_type): bool => $field_type instanceof acf_field);

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

        // Remove ACF's format_value filters and add Timber's transform filters.
        foreach ($field_types as $type => $field_type) {
            \remove_filter("acf/format_value/type={$type}", [$field_type, 'format_value']);
            \add_filter("acf/format_value/type={$type}", $timber_transforms[$type], 10, 3);
        }

        $value = \get_field($field_name, $id, true);

        // Restore ACF's format_value filters and remove Timber's transform filters.
        foreach ($field_types as $type => $field_type) {
            \add_filter("acf/format_value/type={$type}", [$field_type, 'format_value'], 10, 3);
            \remove_filter("acf/format_value/type={$type}", $timber_transforms[$type]);
        }

        return $value;
    }
}
