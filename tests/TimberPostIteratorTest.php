<?php

namespace Timber\Tests;

use PHPUnit\Framework\Attributes\Group;
use Timber\PostArrayObject;

#[Group('posts-api')]
#[Group('post-collections')]
class TimberPostIteratorTest extends TimberIntegrationTestCase
{
    private $collector;

    /**
     * Checks if the 'loop_end' hook runs after last array iteration.
     */
    public function testLoopEndAfterLastItem()
    {
        $pids = static::factory()->post->create_many(3, [
            'post_title' => 'My Post',
        ]);
        $posts = new PostArrayObject($pids);

        $this->collector = [];

        // Later we'll assert that our loop_end hook got called as expected.
        \add_action('loop_end', function () {
            $this->collector[] = 'ended';
        });

        foreach ($posts as $post) {
            $this->collector[] = $post->title;
        }

        $this->assertEquals(['My Post', 'My Post', 'My Post', 'ended'], $this->collector);
    }

    public function testSetupMethodCalled()
    {
        $pids = static::factory()->post->create_many(3);
        $posts = new PostArrayObject($pids);

        // Make sure $wp_query is set up.
        $this->get(\home_url('/'));

        $in_the_loop = false;

        foreach ($posts as $post) {
            global $wp_query;
            $in_the_loop = $in_the_loop || $wp_query->in_the_loop;
        }

        $this->assertTrue($in_the_loop);
    }

    /**
     * Checks if wp_reset_postdata() is run after a query.
     */
    public function testResetPostDataAfterLastItem()
    {
        $pids = static::factory()->post->create_many(3);
        $posts = new PostArrayObject($pids);

        // Make sure $wp_query is set up.
        $this->get(\home_url('/'));

        // Save initial post for later check.
        global $post;
        $initial_post = $post;

        foreach ($posts as $post) {
            // Run something
            $post->title;
        }

        $this->assertEquals($initial_post, $post);
    }

    /**
     * Checks if $wp_query->in_the_loop is reset after a query.
     */
    public function testInTheLoopAfterLastItem()
    {
        $pids = static::factory()->post->create_many(3);
        $posts = new PostArrayObject($pids);

        // Make sure $wp_query is set up.
        $this->get(\home_url('/'));

        foreach ($posts as $post) {
            // Run something
            $post->title;

            global $wp_query;
            $this->assertTrue($wp_query->in_the_loop);
        }

        global $wp_query;

        $this->assertFalse($wp_query->in_the_loop);
    }
}
