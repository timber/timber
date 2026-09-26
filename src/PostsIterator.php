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
    protected ?Post $last_post = null;

    /**
     * Whether `loop_start` fired and `loop_end` has not fired yet. Keys can be post IDs, so the
     * key does not tell where the loop starts or ends.
     */
    private bool $in_loop = false;

    /**
     * Prepares the state before working on a post.
     *
     * Calls the `setup()` function of the current post to setup post data if
     * we’re in the frontend. Before starting the loop, it will call the
     * 'loop_start' hook to improve compatibility with WordPress.
     *
     * @return mixed
     */
    #[ReturnTypeWillChange]
    public function current()
    {
        static $factory;
        $factory ??= new PostFactory();

        if (!$this->valid()) {
            return null;
        }

        // Fire action when the loop has just started.
        if (!$this->in_loop) {
            $this->in_loop = true;

            /**
             * The `loop_start` action is not the only thing we do to improve compatibility with
             * WordPress. There’s more going on in the Timber\Post::setup() function. The
             * compatibility improvements live there, because they also need to work for singular
             * templates, where there’s no loop.
             */
            \do_action_ref_array('loop_start', [&$GLOBALS['wp_query']]);
        }

        // Lazily instantiate a Timber\Post instance exactly once, and keep it for later loops and
        // array access.
        $post = $factory->from(parent::current());
        $this->offsetSet($this->key(), $post);

        if ($post instanceof Post && !\is_admin()) {
            // The setup() method should only run in the frontend.
            //
            // If you run Timber::get_posts() in the WordPress admin, it might
            // overwrite the post global there and mess your posts. By using the
            // is_admin() check, we make sure that this method only runs in the
            // frontend of the website.
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

        parent::next();

        // Fire action when the loop has ended.
        if ($this->in_loop && !$this->valid()) {
            $this->in_loop = false;

            /**
             * The `loop_end` action is not the only thing we do to improve compatibility with
             * WordPress. There’s more going on in the Timber\Post::teardown() function. The
             * compatibility improvements live there, because they also need to work for singular
             * templates, where there’s no loop.
             */
            \do_action_ref_array('loop_end', [&$GLOBALS['wp_query']]);
            \wp_reset_postdata();
        }
    }
}
