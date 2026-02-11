<?php

namespace Timber\Tests;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Ticket;
use Timber\Post;
use Timber\PostQuery;
use Timber\Term;
use Timber\Tests\Support\Attributes\WithOption;
use Timber\Timber;
use Timber\User;

#[Group('posts-api')]
#[Group('post-collections')]
class TimberContextTest extends TimberIntegrationTestCase
{
    /**
     * This throws an infinite loop if memorization isn't working
     */
    public function testContextLoop()
    {
        $this->add_filter_temporarily('timber/context', function ($context) {
            $context = Timber::context();
            $context['zebra'] = 'silly horse';

            return $context;
        });

        $context = Timber::context();

        $this->assertEquals('http://example.org', $context['http_host']);
    }

    public function testPostContextSimple()
    {
        $post_id = static::factory()->post->create();

        $this->get(\get_permalink($post_id));

        $context = Timber::context();
        $post = Timber::get_post($post_id);

        $this->assertArrayNotHasKey('posts', $context);
        $this->assertEquals($post, $context['post']);

        $context = Timber::context();
        $this->assertEquals('http://example.org', $context['http_host']);
    }

    #[WithOption('show_on_front', 'posts')]
    public function testPostsContextHomePosts()
    {
        $id = static::factory()->post->create([
            'post_title' => 'Blog',
            'post_type' => 'page',
        ]);
        $this->setOptionTemporarily('page_for_posts', $id);
        static::factory()->post->create_many(3);
        $this->get('/');

        $context = Timber::context();

        $this->assertInstanceOf(PostQuery::class, $context['posts']);
        $this->assertCount(3, $context['posts']);
        $this->assertInstanceOf(Post::class, $context['post']);
        $this->assertEquals($context['post']->id, $context['posts'][0]->id);
    }

    #[Ticket('https://github.com/timber/timber/issues/2470')]
    #[WithOption('show_on_front', 'posts')]
    #[WithOption('page_for_posts', 0)]
    #[WithOption('page_on_front', 0)]
    public function testPostsContextWithPostOnFrontAndNoPageForPosts()
    {
        $this->get('/');

        $context = Timber::context();

        $this->assertNotContains('post', $context);
    }

    #[WithOption('show_on_front', 'page')]
    public function testPostsContextHomePage()
    {
        $id = static::factory()->post->create([
            'post_type' => 'page',
        ]);
        $this->setOptionTemporarily('page_on_front', $id);
        $this->get('/');

        $context = Timber::context();

        $this->assertArrayNotHasKey('posts', $context);
        $this->assertInstanceOf(Post::class, $context['post']);
        $this->assertEquals($id, $context['post']->id);
    }

    public function testPostsContextSearch()
    {
        static::factory()->post->create_many(3, [
            'post_content' => 'here are some things',
            'post_status' => 'publish',
        ]);
        static::factory()->post->create_many(3, [
            'post_content' => 'here is some stuff',
            'post_status' => 'publish',
        ]);
        $this->get(\home_url('/?s=stuff'));

        $context = Timber::context();

        $this->assertArrayNotHasKey('post', $context);
        $this->assertInstanceOf(PostQuery::class, $context['posts']);
        $this->assertCount(3, $context['posts']);
        $this->assertEquals('stuff', $context['search_query']);
    }

    public function testPostsContextAuthor()
    {
        $uid = static::factory()->user->create([
            'user_login' => 'bob',
        ]);
        static::factory()->post->create_many(3, [
            'post_content' => 'here are some things',
            'post_author' => $uid,
            'post_status' => 'publish',
        ]);
        $this->get(\home_url('/?author=' . $uid));

        $context = Timber::context();

        $this->assertArrayNotHasKey('post', $context);
        $this->assertInstanceOf(PostQuery::class, $context['posts']);
        $this->assertCount(3, $context['posts']);
        $this->assertInstanceOf(User::class, $context['author']);
        $this->assertEquals($uid, $context['author']->id);
    }

    public function testPostsContextCategory()
    {
        $stuff = \wp_insert_term('Stuff', 'category');
        $cat_posts = static::factory()->post->create_many(3, [
            'post_status' => 'publish',
        ]);
        foreach ($cat_posts as $id) {
            \wp_set_object_terms($id, $stuff, 'category');
        }

        // 3 uncategorized posts
        static::factory()->post->create_many(3, [
            'post_status' => 'publish',
        ]);

        $this->get(\home_url('/?cat=' . $stuff['term_id']));

        $context = Timber::context();

        $this->assertArrayNotHasKey('post', $context);
        $this->assertInstanceOf(PostQuery::class, $context['posts']);
        $this->assertCount(3, $context['posts']);

        $this->assertInstanceOf(Term::class, $context['term']);
        $this->assertEquals('Stuff', $context['term']->title());
    }

    public function testPostsContextTag()
    {
        $stuff = \wp_insert_term('Stuff', 'post_tag');
        $cat_posts = static::factory()->post->create_many(3, [
            'post_status' => 'publish',
        ]);
        foreach ($cat_posts as $id) {
            \wp_set_object_terms($id, $stuff, 'post_tag');
        }

        // 3 untagged posts
        static::factory()->post->create_many(3, [
            'post_status' => 'publish',
        ]);

        $this->get(\home_url('/?tag=stuff'));

        $context = Timber::context();

        $this->assertArrayNotHasKey('post', $context);
        $this->assertInstanceOf(PostQuery::class, $context['posts']);
        $this->assertCount(3, $context['posts']);

        $this->assertInstanceOf(Term::class, $context['term']);
        $this->assertEquals('Stuff', $context['term']->title());
    }

    public function testPostsContextTax()
    {
        \register_taxonomy('thingy', ['post'], [
            'public' => true,
        ]);
        $stuff = \wp_insert_term('Stuff', 'thingy');
        $cat_posts = static::factory()->post->create_many(3, [
            'post_status' => 'publish',
        ]);
        foreach ($cat_posts as $id) {
            \wp_set_object_terms($id, $stuff, 'thingy');
        }

        // 3 non-thingy posts
        static::factory()->post->create_many(3, [
            'post_status' => 'publish',
        ]);

        $this->get(\home_url('/?thingy=stuff'));

        $context = Timber::context();

        $this->assertArrayNotHasKey('post', $context);
        $this->assertInstanceOf(PostQuery::class, $context['posts']);
        $this->assertCount(3, $context['posts']);

        $this->assertInstanceOf(Term::class, $context['term']);
        $this->assertEquals('Stuff', $context['term']->title());
    }

    public function testIfSetupFunctionIsRunInSingularTemplates()
    {
        $post_id = static::factory()->post->create();
        $this->get(\get_permalink($post_id));

        global $wp_query;

        // Reset loop state to test that Timber::context() sets it up.
        // WP 6.3 sets in_the_loop=true during request setup due to a workaround
        // that was fixed in WP 6.4. See: https://core.trac.wordpress.org/ticket/58154
        $wp_query->in_the_loop = false;

        $this->assertFalse($wp_query->in_the_loop);

        Timber::context();

        $this->assertTrue($wp_query->in_the_loop);
    }

    /**
     * Tests whether 'the_post' action is called when a singular template is displayed.
     *
     * @see TestTimberPost::testPostConstructorAndThePostHook()
     */
    public function testIfThePostHookIsRunInSingularTemplates()
    {
        \add_action('the_post', function ($post) {
            \add_filter('touched_the_post_action', '__return_true');
        });

        $post_id = static::factory()->post->create();
        $this->get(\get_permalink($post_id));

        Timber::context();

        $this->assertTrue(\apply_filters('touched_the_post_action', false));
    }

    public function testContext()
    {
        $context = Timber::context();
        $this->assertEquals('http://example.org', $context['http_host']);
    }
}
