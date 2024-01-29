<?php

namespace Timber\Factory;

use InvalidArgumentException;
use Timber\Attachment;
use Timber\CoreInterface;
use Timber\Helper;
use Timber\Image;
use Timber\PathHelper;
use Timber\Post;
use Timber\PostArrayObject;
use Timber\PostQuery;
use WP_Post;
use WP_Query;

/**
 * Internal API class for instantiating posts
 */
class PostFactory
{
    public function from($params)
    {
        if (\is_int($params) || \is_string($params) && \is_numeric($params)) {
            return $this->from_id((int) $params);
        }

        if ($params instanceof WP_Query) {
            return $this->from_wp_query($params);
        }

        if (\is_object($params)) {
            return $this->from_post_object($params);
        }

        if ($this->is_numeric_array($params)) {
            return new PostArrayObject(\array_map([$this, 'from'], $params));
        }

        if (\is_array($params) && !empty($params['ID'])) {
            return $this->from_id($params['ID']);
        }

        if (\is_array($params)) {
            return $this->from_wp_query(new WP_Query($params));
        }

        return null;
    }

    protected function from_id(int $id): ?Post
    {
        $wp_post = \get_post($id);

        if (!$wp_post) {
            return null;
        }

        return $this->build($wp_post);
    }

    protected function from_post_object(object $obj): CoreInterface
    {
        if ($obj instanceof CoreInterface) {
            return $obj;
        }

        if ($obj instanceof WP_Post) {
            return $this->build($obj);
        }

        throw new InvalidArgumentException(\sprintf(
            'Expected an instance of Timber\CoreInterface or WP_Post, got %s',
            \get_class($obj)
        ));
    }

    protected function from_wp_query(WP_Query $query): iterable
    {
        return new PostQuery($query);
    }

    protected function get_post_class(WP_Post $post): string
    {
        /**
         * Pseudo filter that checks whether the non-usable filter was used.
         *
         * @deprecated 2.0.0, use `timber/post/classmap`
         */
        if ('deprecated' !== \apply_filters('Timber\PostClassMap', 'deprecated')) {
            Helper::doing_it_wrong(
                'The `Timber\PostClassMap` filter',
                'Use the `timber/post/classmap` filter instead.',
                '2.0.0'
            );
        }

        /**
         * Filters the class(es) used for different post types.
         *
         * Read more about this in the documentation for [Post Class Maps](https://timber.github.io/docs/v2/guides/class-maps/#the-post-class-map).
         *
         * The default Post Class Map will contain class names for posts, pages that map to
         * `Timber\Post` and a callback that will map attachments to `Timber\Attachment` and
         * attachments that are images to `Timber\Image`.
         *
         * Make sure to merge in your additional classes instead of overwriting the whole Class Map.
         *
         * @since 2.0.0
         * @example
         * ```
         * use Book;
         * use Page;
         *
         * add_filter( 'timber/post/classmap', function( $classmap ) {
         *     $custom_classmap = [
         *         'page' => Page::class,
         *         'book' => Book::class,
         *     ];
         *
         *     return array_merge( $classmap, $custom_classmap );
         * } );
         * ```
         *
         * @param array $classmap The post class(es) to use. An associative array where the key is
         *                        the post type and the value the name of the class to use for this
         *                        post type or a callback that determines the class to use.
         */
        $classmap = \apply_filters('timber/post/classmap', [
            'post' => Post::class,
            'page' => Post::class,
            // Apply special logic for attachments.
            'attachment' => function (WP_Post $attachment) {
                return $this->is_image($attachment) ? Image::class : Attachment::class;
            },
        ]);

        $class = $classmap[$post->post_type] ?? null;

        // If class is a callable, call it to get the actual class name
        if (\is_callable($class)) {
            $class = $class($post);
        }

        $class = $class ?? Post::class;

        /**
         * Filters the post class based on your custom criteria.
         *
         * Maybe you want to set a custom class based upon how blocks are used?
         * This allows you to filter the PHP class, utilizing data from the WP_Post object.
         *
         * @since 2.0.0
         * @example
         * ```
         * add_filter( 'timber/post/class', function( $class, $post ) {
         *     if ( has_blocks($post) ) {
         *         return GutenbergPost::class;
         *     }
         *
         *     return $class;
         * }, 10, 2 );
         * ```
         *
         * @param string $class The class to use.
         * @param WP_Post $post The post object.
         */
        $class = \apply_filters('timber/post/class', $class, $post);

        return $class;
    }

    protected function is_image(WP_Post $post)
    {
        $src = \get_attached_file($post->ID);
        $mimes = \wp_get_mime_types();
        // Add mime types that Timber recongizes as images, regardless of config
        $mimes['svg'] = 'image/svg+xml';
        $mimes['webp'] = 'image/webp';
        $check = \wp_check_filetype(PathHelper::basename($src), $mimes);

        /**
         * Filters the list of image extensions that will be used to determine if an attachment is an image.
         *
         * You can use this filter to add or remove image extensions to the list of extensions that will be
         * used to determine if an attachment is an image.
         *
         * @param array $extensions An array of image extensions.
         * @since 2.0.0
         */
        $extensions = \apply_filters('timber/post/image_extensions', [
            'jpg',
            'jpeg',
            'jpe',
            'gif',
            'png',
            'svg',
            'webp',
        ]);

        return \in_array($check['ext'], $extensions);
    }

    protected function build(WP_Post $post): CoreInterface
    {
        $class = $this->get_post_class($post);

        return $class::build($post);
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
}
