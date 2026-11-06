<?php

namespace Timber\Tests;

use PHPUnit\Framework\Attributes\Group;
use Timber\Post;
use Timber\PostArrayObject;
use Timber\Timber;

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

    public function testLoopsReuseTheRealizedPosts()
    {
        $pids = static::factory()->post->create_many(3);
        $posts = Timber::get_posts([
            'post__in' => $pids,
            'orderby' => 'post__in',
        ]);

        $first_loop = [];
        foreach ($posts as $post) {
            $post->enriched = 'by controller';
            $first_loop[] = $post;
        }
        $second_loop = [];
        foreach ($posts as $post) {
            $second_loop[] = $post;
        }

        $this->assertSame($first_loop, $second_loop);
        $this->assertSame($first_loop[0], $posts[0]);
        $this->assertSame('by controller', $second_loop[2]->enriched);
    }

    public function testToArrayAfterEarlyBreakReusesTheRealizedPost()
    {
        $pids = static::factory()->post->create_many(2);
        $posts = Timber::get_posts([
            'post__in' => $pids,
            'orderby' => 'post__in',
        ]);

        foreach ($posts as $post) {
            break;
        }
        $array = $posts->to_array();

        $this->assertSame($post, $array[0]);
        $this->assertContainsOnlyInstancesOf(Post::class, $array);
    }

    public function testLoopOverIdsLeavesTheIdsInTheCollection()
    {
        $pids = static::factory()->post->create_many(2);
        $posts = new PostArrayObject($pids);

        foreach ($posts as $post) {
            $this->assertSame($pids[0], $post->ID);
            break;
        }

        $this->assertSame($pids[0], $posts[0]);
        $this->assertSame($pids, $posts->to_array());
    }

    public function testLoopOverMissingIdKeepsTheId()
    {
        $pid = static::factory()->post->create();
        $missing = $pid + 1000;
        $posts = new PostArrayObject([$pid, $missing]);

        $loop = [];
        foreach ($posts as $post) {
            $loop[] = $post;
        }

        $this->assertSame($pid, $loop[0]->ID);
        $this->assertNull($loop[1]);
        $this->assertSame([$pid, $missing], $posts->to_array());
    }

    public function testLoopOverChildrenInAdminKeepsTheGlobalPost()
    {
        $parent_id = static::factory()->post->create([
            'post_type' => 'page',
        ]);
        static::factory()->post->create_many(2, [
            'post_type' => 'page',
            'post_parent' => $parent_id,
        ]);
        static::factory()->post->create();
        $this->get(\home_url('/'));

        global $post, $wp_query;
        // Like a column in the list table, where WordPress sets the global post for each row.
        $post = \get_post($parent_id);
        $this->assertNotSame($parent_id, $wp_query->post->ID);
        require_once ABSPATH . 'wp-admin/includes/class-wp-screen.php';
        require_once ABSPATH . 'wp-admin/includes/screen.php';
        \set_current_screen('edit-page');

        try {
            foreach (Timber::get_post($parent_id)->children() as $child) {
                $child->title;
            }
        } finally {
            \set_current_screen('front');
        }

        $this->assertSame($parent_id, $post->ID);
    }

    public function testIteratorCountFiresNoLoopHooks()
    {
        $pids = static::factory()->post->create_many(2);
        $iterator = (new PostArrayObject($pids))->getIterator();
        $this->collect_loop_hooks();

        $this->assertSame(2, \iterator_count($iterator));
        $this->assertSame([], $this->collector);

        foreach ($iterator as $post) {
            $this->collector[] = $post->ID;
        }

        $this->assertSame(['start', ...$pids, 'end'], $this->collector);
    }

    public function testSeekPastTheFirstPostStartsTheLoopThere()
    {
        $pids = static::factory()->post->create_many(3);
        $iterator = (new PostArrayObject($pids))->getIterator();
        $this->collect_loop_hooks();

        $iterator->seek(1);
        while ($iterator->valid()) {
            $this->collector[] = $iterator->current()->ID;
            $iterator->next();
        }

        $this->assertSame(['start', $pids[1], $pids[2], 'end'], $this->collector);
    }

    public function testLoopOverChildrenFiresLoopHooksAndRestoresPost()
    {
        $parent_id = static::factory()->post->create([
            'post_type' => 'page',
        ]);
        static::factory()->post->create_many(2, [
            'post_type' => 'page',
            'post_parent' => $parent_id,
        ]);
        $this->get(\get_permalink($parent_id));
        $children = Timber::get_post($parent_id)->children();
        $this->collect_loop_hooks();

        foreach ($children as $child) {
            $this->collector[] = $child->ID;
        }

        global $post, $wp_query;
        $this->assertSame(['start', ...\array_keys($children->to_array()), 'end'], $this->collector);
        $this->assertSame($parent_id, $post->ID);
        $this->assertFalse($wp_query->in_the_loop);
    }

    public function testLoopOverEmptyCollectionFiresNoLoopHooks()
    {
        $this->collect_loop_hooks();

        foreach (new PostArrayObject([]) as $post) {
            $this->collector[] = $post;
        }

        $this->assertSame([], $this->collector);
    }

    public function testTraversalWithoutRewindFiresLoopHooksOncePerPass()
    {
        $pids = static::factory()->post->create_many(2);
        $iterator = (new PostArrayObject($pids))->getIterator();
        $this->collect_loop_hooks();

        while ($iterator->valid()) {
            $this->collector[] = $iterator->current()->ID;
            $iterator->next();
        }
        $iterator->next();
        $iterator->seek(0);
        while ($iterator->valid()) {
            $this->collector[] = $iterator->current()->ID;
            $iterator->next();
        }

        $this->assertSame(['start', ...$pids, 'end', 'start', ...$pids, 'end'], $this->collector);
    }

    public function testRestartAfterEarlyBreakStartsANewLoop()
    {
        $pids = static::factory()->post->create_many(2);
        $iterator = (new PostArrayObject($pids))->getIterator();
        $this->collect_loop_hooks();

        foreach ($iterator as $post) {
            $this->collector[] = $post->ID;
            break;
        }
        foreach ($iterator as $post) {
            $this->collector[] = $post->ID;
        }
        $iterator->seek(0);
        $this->collector[] = $iterator->current()->ID;
        $iterator->seek(0);
        while ($iterator->valid()) {
            $this->collector[] = $iterator->current()->ID;
            $iterator->next();
        }

        $this->assertSame([
            'start', $pids[0],
            'start', ...$pids, 'end',
            'start', $pids[0],
            'start', ...$pids, 'end',
        ], $this->collector);
    }

    public function testCurrentPastTheEndLeavesCollectionUnchanged()
    {
        $pid = static::factory()->post->create();
        $empty = (new PostArrayObject([]))->getIterator();
        $exhausted = (new PostArrayObject([$pid]))->getIterator();
        foreach ($exhausted as $post) {
            $post->title;
        }
        $this->collect_loop_hooks();

        $this->assertNull($empty->current());
        $this->assertNull($exhausted->current());
        $this->assertSame([0, false], [\count($empty), $empty->valid()]);
        $this->assertSame([1, false], [\count($exhausted), $exhausted->valid()]);
        $this->assertSame([], $this->collector);
    }

    private function collect_loop_hooks(): void
    {
        $this->collector = [];
        $this->add_action_temporarily('loop_start', function () {
            $this->collector[] = 'start';
        });
        $this->add_action_temporarily('loop_end', function () {
            $this->collector[] = 'end';
        });
    }
}
