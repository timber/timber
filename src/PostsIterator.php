<?php

namespace Timber;

use ArrayIterator;
use ReturnTypeWillChange;
use Timber\Factory\PostFactory;

/**
 * Class PostsIterator
 */
class PostsIterator extends ArrayIterator
{
    /**
     * @var null|Post The last post that was returned by the iterator. Used
     *                   to skip the logic in `current()`.
     */
    protected ?Post $last_post;

    /**
     * Prepares the state before working on a post.
     *
     * Calls the `setup()` function of the current post to setup post data. Before starting the
     * loop, it will call the 'loop_start' hook to improve compatibility with WordPress.
     *
     * @return mixed
     */
    #[ReturnTypeWillChange]
    public function current()
    {
        static $factory;
        $factory = $factory ?? new PostFactory();

        // Fire action when the loop has just started.
        if (0 === $this->key()) {
            /**
             * The `loop_start` action is not the only thing we do to improve compatibility with
             * WordPress. There’s more going on in the Timber\Post::setup() function. The
             * compatibility improvements live there, because they also need to work for singular
             * templates, where there’s no loop.
             */
            \do_action_ref_array('loop_start', [&$GLOBALS['wp_query']]);
        }

        $wp_post = parent::current();

        // Lazily instantiate a Timber\Post instance exactly once.
        $post = $factory->from($wp_post);

        if ($post instanceof Post) {
            $post->setup();
        }

        $this->last_post = $post;

        return $post;
    }

    /**
     * Cleans up state before advancing to the next post.
     *
     * Calls the `teardown()` function of the current post. In the last run of a loop through posts,
     * it will call the 'loop_end' hook to improve compatibility with WordPress.
     *
     * @since 2.0.0
     */
    public function next(): void
    {
        /**
         * Load from $last_post instead of $this->current(), because $this->current() would call
         * $post->setup() again.
         */
        $post = $this->last_post;

        if ($post instanceof Post) {
            $post->teardown();
        }

        // Fire action when the loop has ended.
        if ($this->key() === $this->count() - 1) {
            /**
             * The `loop_end` action is not the only thing we do to improve compatibility with
             * WordPress. There’s more going on in the Timber\Post::teardown() function. The
             * compatibility improvements live there, because they also need to work for singular
             * templates, where there’s no loop.
             */
            \do_action_ref_array('loop_end', [&$GLOBALS['wp_query']]);
            \wp_reset_postdata();
        }

        parent::next();
    }
}
