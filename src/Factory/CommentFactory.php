<?php

namespace Timber\Factory;

use InvalidArgumentException;
use Timber\Comment;
use Timber\CoreInterface;
use WP_Comment;
use WP_Comment_Query;

/**
 * Internal API class for instantiating Comments
 */
class CommentFactory
{
    public function from($params)
    {
        if (\is_int($params) || \is_string($params) && \is_numeric($params)) {
            return $this->from_id((int) $params);
        }

        if ($params instanceof WP_Comment_Query) {
            return $this->from_wp_comment_query($params);
        }

        if (\is_object($params)) {
            return $this->from_comment_object($params);
        }

        if ($this->is_numeric_array($params)) {
            return \array_map([$this, 'from'], $params);
        }

        if (\is_array($params)) {
            return $this->from_wp_comment_query(new WP_Comment_Query($params));
        }
    }

    protected function from_id(int $id)
    {
        $wp_comment = \get_comment($id);

        if (!$wp_comment) {
            return null;
        }

        return $this->build($wp_comment);
    }

    protected function from_comment_object(object $comment): CoreInterface
    {
        if ($comment instanceof CoreInterface) {
            // We already have some kind of Timber Core object
            return $comment;
        }

        if ($comment instanceof WP_Comment) {
            return $this->build($comment);
        }

        throw new InvalidArgumentException(\sprintf(
            'Expected an instance of Timber\CoreInterface or WP_Comment, got %s',
            \get_class($comment)
        ));
    }

    protected function from_wp_comment_query(WP_Comment_Query $query): iterable
    {
        return \array_map([$this, 'build'], $query->get_comments());
    }

    protected function get_comment_class(WP_Comment $comment): string
    {
        /**
         * Filters the class(es) used for comments linked to different post types.
         *
         * The default class is Timber\Comment. You can use this filter to provide your own comment class for specific post types.
         *
         * Make sure to merge in your additional classes instead of overwriting the whole Class Map.
         *
         * @since 2.0.0
         * @example
         * ```
         * use Book;
         *
         * add_filter( 'timber/post/classmap', function( $classmap ) {
         *     $custom_classmap = [
         *         'book' => BookComment::class,
         *     ];
         *
         *     return array_merge( $classmap, $custom_classmap );
         * } );
         * ```
         *
         * @param array $classmap The post class(es) to use. An associative array where the key is
         *                        the post type and the value the name of the class to use for the comments
         *                        of this post type or a callback that determines the class to use.
         */
        $map = \apply_filters('timber/comment/classmap', []);

        $type = \get_post_type($comment->comment_post_ID);
        $class = $map[$type] ?? null;

        if (\is_callable($class)) {
            $class = $class($comment);
        }

        $class = $class ?? Comment::class;

        /**
         * Filters the comment class based on your custom criteria.
         *
         * Maybe you want to set a custom class based upon the comment type?
         * This allows you to filter the PHP class, utilizing data from the WP_Comment object.
         *
         * @since 2.0.0
         * @example
         * ```
         * add_filter( 'timber/comment/class', function( $class, $comment ) {
         *     if ( $comment->comment_type === 'pingback' ) {
         *         return PingBackComment::class;
         *     }
         *     return $class;
         * }, 10, 2 );
         * ```
         *
         * @param string $class The class to use.
         * @param WP_Comment $comment The comment object.
         */
        $class = \apply_filters('timber/comment/class', $class, $comment);

        return $class;
    }

    protected function build(WP_Comment $comment): CoreInterface
    {
        $class = $this->get_comment_class($comment);

        return $class::build($comment);
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
