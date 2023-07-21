<?php

use Timber\LocationManager;
use Timber\Post;
use Timber\PostArrayObject;

/**
 * @group posts-api
 * @group terms-api
 * @group users-api
 * @group post-collections
 */
class TestTimberMainClass extends Timber_UnitTestCase
{
    public function testGetPostNumeric()
    {
        $post_id = $this->factory->post->create();
        $post = Timber::get_post($post_id);
        $this->assertEquals('Timber\Post', get_class($post));
    }

    public function testGetPostBySlug()
    {
        $this->factory->post->create([
            'post_name' => 'kill-bill',
        ]);

        $post = Timber\Timber::get_post_by('slug', 'kill-bill');

        $this->assertEquals('kill-bill', $post->post_name);
    }

    public function testGetPostBySlugNewest()
    {
        $post_id = $this->factory->post->create([
            'post_type' => 'post',
            'post_name' => 'privacy',
            'post_date' => '2018-01-10 02:58:18',
        ]);

        $page_id = $this->factory->post->create([
            'post_type' => 'page',
            'post_name' => 'privacy',
            'post_date' => '2020-01-10 02:58:18',
        ]);

        $post = Timber\Timber::get_post_by('slug', 'privacy', [
            'order' => 'DESC',
        ]);

        $this->assertEquals('privacy', $post->post_name);
        $this->assertEquals($page_id, $post->ID);
    }

    public function testGetPostBySlugAndPostType()
    {
        register_post_type('movie', [
            'public' => true,
        ]);

        $post_id_movie = $this->factory->post->create([
            'post_name' => 'kill-bill',
            'post_type' => 'movie',
        ]);
        $post_id_page = $this->factory->post->create([
            'post_name' => 'kill-bill',
            'post_type' => 'page',
        ]);

        $post_movie = Timber\Timber::get_post_by('slug', 'kill-bill', [
            'post_type' => 'movie',
        ]);
        $post_page = Timber\Timber::get_post_by('slug', 'kill-bill', [
            'post_type' => 'page',
        ]);

        $this->assertEquals($post_id_movie, $post_movie->ID);
        $this->assertEquals($post_id_page, $post_page->ID);

        $post_any = Timber\Timber::get_post_by('slug', 'kill-bill');
        $this->assertEquals($post_id_movie, $post_any->ID);
    }

    public function testGetPostBySlugForNonexistentPost()
    {
        $this->factory->post->create([
            'post_name' => 'kill-bill',
        ]);

        $post = Timber\Timber::get_post_by('slug', 'kill-bill-2');

        $this->assertSame(null, $post);
    }

    public function testGetPostByTitle()
    {
        $post_title = 'A Post Title containing Special Characters like # or ! or Ä or ç';
        $this->factory->post->create([
            'post_title' => $post_title,
        ]);

        $post = Timber\Timber::get_post_by('title', $post_title);

        $this->assertEquals($post_title, $post->title());
    }

    public function testGetPostByTitleWithDifferentCasing()
    {
        $post_title = 'A Post Title containing Special Characters like # or ! or Ä or ç';
        $this->factory->post->create([
            'post_title' => $post_title,
        ]);

        $lower_case_title = mb_strtolower($post_title);
        $post = Timber\Timber::get_post_by('title', $lower_case_title);

        $this->assertEquals($post_title, $post->title());
    }

    public function testGetPostByTitleAndPostType()
    {
        register_post_type('book', [
            'public' => true,
        ]);
        register_post_type('movie', [
            'public' => true,
        ]);
        $post_title = 'A Special Post Title containing Special Characters like # or ! or Ä or ç';

        $post_id_movie = $this->factory->post->create([
            'post_title' => $post_title,
            'post_type' => 'movie',
            'post_date' => '2020-01-10 02:58:18',
        ]);

        $post_id_book = $this->factory->post->create([
            'post_title' => $post_title,
            'post_type' => 'book',
            'post_date' => '2020-01-13 02:58:18',
        ]);

        $post_id_page = $this->factory->post->create([
            'post_title' => $post_title,
            'post_type' => 'page',
            'post_date' => '2020-01-02 02:58:18',
        ]);

        $post_movie = Timber\Timber::get_post_by('title', $post_title, [
            'post_type' => 'movie',
        ]);
        $post_page = Timber\Timber::get_post_by('title', $post_title, [
            'post_type' => 'page',
        ]);
        $post_multiple = Timber\Timber::get_post_by('title', $post_title, [
            'post_type' => ['page', 'book'],
        ]);
        $post_multiple_any = Timber\Timber::get_post_by('title', $post_title, [
            'post_type' => 'any',
        ]);

        $this->assertEquals($post_id_movie, $post_movie->ID);
        $this->assertEquals($post_id_page, $post_page->ID);

        // Multiple post types should return the post with the oldest post date.
        $this->assertEquals($post_id_page, $post_multiple->ID);
        $this->assertEquals($post_id_page, $post_multiple_any->ID);
    }

    public function testGetPostByTitleForNonexistentPost()
    {
        $this->factory->post->create();

        $post = Timber\Timber::get_post_by('title', 'Just a nonexistent post');

        $this->assertSame(null, $post);
    }

    public function testGetPostFromPostObject()
    {
        $pid = $this->factory->post->create();
        $wp_post = get_post($pid);

        $this->register_post_classmap_temporarily([
            'post' => TimberAlert::class,
        ]);

        $post = Timber::get_post($wp_post);
        $this->assertInstanceOf(TimberAlert::class, $post);
    }

    public function testGetPostFromQueryArray()
    {
        $pid = $this->factory->post->create();

        $this->register_post_classmap_temporarily([
            'post' => TimberAlert::class,
        ]);

        $this->assertInstanceOf(TimberAlert::class, Timber::get_post([
            'post_type' => 'post',
        ]));
    }

    public function testGetPostsFromQueryArray()
    {
        $pid = $this->factory->post->create();

        $this->register_post_classmap_temporarily([
            'post' => TimberAlert::class,
        ]);

        $posts = Timber::get_posts([
            'post_type' => 'post',
        ]);

        $this->assertInstanceOf(TimberAlert::class, $posts[0]);
    }

    public function testGetPostWithCustomPostType()
    {
        register_post_type('event', [
            'public' => true,
        ]);

        $event_id = $this->factory->post->create([
            'post_type' => 'event',
        ]);
        $this->register_post_classmap_temporarily([
            'event' => TimberAlert::class,
        ]);

        $this->assertInstanceOf(TimberAlert::class, Timber::get_post($event_id));
    }

    public function testGetPostWithCustomPostTypeNotPublic()
    {
        register_post_type('event', [
            'public' => false,
        ]);
        $pid = $this->factory->post->create([
            'post_type' => 'event',
        ]);

        $this->register_post_classmap_temporarily([
            'event' => TimberAlert::class,
        ]);

        $this->assertInstanceOf(TimberAlert::class, Timber::get_post($pid));
    }

    public function testGetPostsQueryArrayDefault()
    {
        $this->factory->post->create();

        $posts = Timber::get_posts([
            'post_type' => 'post',
        ]);

        $this->assertInstanceOf(Post::class, $posts[0]);
    }

    public function testGetPostsFromArrayOfIds()
    {
        $pids = [
            $this->factory->post->create(),
            $this->factory->post->create(),
            $this->factory->post->create(),
        ];
        $posts = Timber::get_posts($pids);

        $this->assertCount(3, $posts);
        $this->assertInstanceOf(PostArrayObject::class, $posts);
        foreach ($posts as $post) {
            $this->assertInstanceOf(Post::class, $post);
        }
    }

    public function testUserInContextAnon()
    {
        $context = Timber::context();
        $this->assertArrayHasKey('user', $context);
        $this->assertFalse($context['user']);
    }

    public function testUserInContextLoggedIn()
    {
        $uid = $this->factory->user->create([
            'user_login' => 'timber',
            'user_pass' => 'timber',
        ]);
        $user = wp_set_current_user($uid);

        $context = Timber::context();
        $this->assertArrayHasKey('user', $context);
        $this->assertInstanceOf('Timber\User', $context['user']);
    }

    public function testQueryPostsInContext()
    {
        $pids = $this->factory->post->create_many(20);
        $this->go_to('/');

        $context = Timber::context();

        $this->assertInstanceOf(Timber\PostQuery::class, $context['posts']);
    }

    public function testContextWithExtraArgs()
    {
        $pids = $this->factory->post->create_many(20);
        $this->go_to('/');

        $context = Timber::context([
            'extra' => 'stuff',
            'fancy' => [
                'this' => 'can',
                'be' => 'whatever',
            ],
        ]);

        $this->assertEquals('stuff', $context['extra']);
        $this->assertEquals([
            'this' => 'can',
            'be' => 'whatever',
        ], $context['fancy']);
        $this->assertInstanceOf(Timber\PostQuery::class, $context['posts']);

        // Underlying context is immutable and unaffected by extra data.
        $this->assertFalse(array_key_exists('extra', Timber::context()));
        $this->assertFalse(array_key_exists('fancy', Timber::context()));
    }

    public function testGetPostsWithClassMap()
    {
        register_post_type('portfolio', [
            'public' => true,
        ]);
        register_post_type('alert', [
            'public' => true,
        ]);
        $this->factory->post->create([
            'post_type' => 'portfolio',
            'post_title' => 'A portfolio item',
            'post_date' => '2015-04-23 15:13:52',
        ]);
        $this->factory->post->create([
            'post_type' => 'alert',
            'post_title' => 'An alert',
            'post_date' => '2015-06-23 15:13:52',
        ]);

        $this->register_post_classmap_temporarily([
            'alert' => TimberAlert::class,
            'portfolio' => TimberPortfolio::class,
        ]);

        $posts = Timber::get_posts([
            'post_type' => 'any',
        ]);

        $this->assertInstanceOf(TimberAlert::class, $posts[0]);
        $this->assertInstanceOf(TimberPortfolio::class, $posts[1]);
    }

    public function testGetPostWithClassMap()
    {
        register_post_type('portfolio', [
            'public' => true,
        ]);
        register_post_type('alert', [
            'public' => true,
        ]);
        $portfolio_id = $this->factory->post->create([
            'post_type' => 'portfolio',
            'post_title' => 'A portfolio item',
            'post_date' => '2015-04-23 15:13:52',
        ]);
        $alert_id = $this->factory->post->create([
            'post_type' => 'alert',
            'post_title' => 'An alert',
            'post_date' => '2015-06-23 15:13:52',
        ]);

        $this->register_post_classmap_temporarily([
            'alert' => TimberAlert::class,
            'portfolio' => TimberPortfolio::class,
        ]);

        $this->assertInstanceOf(TimberPortfolio::class, Timber::get_post($portfolio_id));
        $this->assertInstanceOf(TimberAlert::class, Timber::get_post($alert_id));
    }

    public function testGetTerms()
    {
        $posts = $this->factory->post->create_many(15, [
            'post_type' => 'post',
        ]);
        $tags = [];
        foreach ($posts as $post) {
            $tag = rand_str();
            wp_set_object_terms($post, $tag, 'post_tag');
            $tags[] = $tag;
        }
        sort($tags);
        $terms = Timber::get_terms('tag');
        $this->assertEquals('Timber\Term', get_class($terms[0]));
        $results = [];
        foreach ($terms as $term) {
            $results[] = $term->name;
        }
        sort($results);
        $this->assertEquals($results, $tags);
    }

    public function testTimberRenderString()
    {
        $pid = $this->factory->post->create([
            'post_title' => 'Zoogats',
        ]);
        $post = Timber::get_post($pid);
        ob_start();
        Timber::render_string('<h2>{{post.title}}</h2>', [
            'post' => $post,
        ]);
        $data = ob_get_contents();
        ob_end_clean();
        $this->assertEquals('<h2>Zoogats</h2>', trim($data));
    }

    public function testTimberRender()
    {
        $pid = $this->factory->post->create([
            'post_title' => 'Foobar',
        ]);
        $post = Timber::get_post($pid);
        ob_start();
        Timber::render('assets/single-post.twig', [
            'post' => $post,
        ]);
        $data = ob_get_contents();
        ob_end_clean();
        $this->assertEquals('<h1>Foobar</h1>', trim($data));
    }

    public function testTimberGetCallingScriptFile()
    {
        $calling_file = LocationManager::get_calling_script_file();
        $file = getcwd() . '/tests/test-timber.php';
        $this->assertEquals($calling_file, $file);
    }

    public function testCompileNull()
    {
        $str = Timber::compile('assets/single-course.twig', null);
        $this->assertEquals('I am single course', $str);
    }

    public function testCompileAbsolutePath()
    {
        $str = Timber::compile(__DIR__ . '/assets/single.twig', null);
        $this->assertEquals('I am single.twig', trim($str));
    }

    /**
       * @ticket 1660
       */
    public function testDoubleInstantiationOfSubclass()
    {
        $post_id = $this->factory->post->create([
            'post_type' => 'person',
        ]);

        $this->register_post_classmap_temporarily([
            'person' => Person::class,
        ]);

        $this->assertInstanceOf(Person::class, Timber::get_post($post_id));
    }

    /**
     * @ticket 1660
     */
    public function testDoubleInstantiationOfTimberPostClass()
    {
        $post_id = $this->factory->post->create([
            'post_type' => 'post',
        ]);
        // Unlike above, do NOT register a special Class Map.
        $this->assertInstanceOf(Post::class, Timber::get_post($post_id));
    }

    /**
     * @expectedIncorrectUsage Timber::get_posts()
     */
    public function testNumberPostsAll()
    {
        $this->factory->post->create_many(17);

        $posts = Timber::get_posts([
            'post_type' => 'post',
            'numberposts' => 17,
        ]);
        $this->assertSame(10, count($posts));
    }

    public function testPostsPerPage()
    {
        $pids = $this->factory->post->create_many(15);

        $posts = Timber::get_posts([
            'post_type' => 'post',
            'posts_per_page' => 7,
        ]);

        $this->assertCount(7, $posts);
    }

    public function testPostsPerPageAll()
    {
        $pids = $this->factory->post->create_many(23);

        $posts = Timber::get_posts([
            'post_type' => 'post',
            'posts_per_page' => -1,
        ]);

        $this->assertCount(23, $posts);
    }

    public function testPostsPerPageBig()
    {
        $pids = $this->factory->post->create_many(15);

        $posts = Timber::get_posts([
            'post_type' => 'post',
            'posts_per_page' => 15,
        ]);

        $this->assertCount(15, $posts);
    }

    /**
     * @expectedDeprecated Timber::query_post()
     */
    public function testBlankQueryPost()
    {
        $pid = $this->factory->post->create();
        $this->go_to(home_url('/?p=' . $pid));
        $post = Timber::query_post();
        $this->assertEquals($pid, $post->ID);
    }

    public function testGetPostWithMergeDefault()
    {
        $cat = $this->factory->term->create([
            'taxonomy' => 'category',
        ]);

        // Create some irrelevant posts
        $this->factory->post->create_many(3);

        $id = $this->factory->post->create([
            'post_category' => [$cat],
        ]);

        // Create a few other irrelevant posts
        $this->factory->post->create_many(3);

        // Mutate the global query for the Meow cat
        query_posts([
            'category__in' => [$cat],
        ]);

        // Because we're merging the default query_vars, this query should
        // return ONLY those posts categorized under "meow"
        $post = Timber::get_post([
            'post_type' => 'post',
        ], [
            'merge_default' => true,
        ]);

        $this->assertEquals($id, $post->id);
    }

    public function testGetPostsWithMergeDefault()
    {
        $cat = $this->factory->term->create([
            'taxonomy' => 'category',
        ]);

        $post_ids = $this->factory->post->create_many(3, [
            'post_category' => [$cat],
        ]);

        // Create a few other irrelevant posts
        $this->factory->post->create_many(5);

        // Mutate the global query for the Meow cat
        query_posts([
            'category__in' => [$cat],
        ]);

        // Because we're merging the default query_vars, this query should
        // return ONLY those posts categorized under "meow"
        $posts = Timber::get_posts([
            'post_type' => 'post',
        ], [
            'merge_default' => true,
        ]);

        $this->assertCount(3, $posts);
    }

    /**
     * @group wp_query_hacks
     */
    public function testGettingWithCatAndOtherStuff()
    {
        $pids = $this->factory->post->create_many(6);
        $cat = $this->factory->term->create([
            'name' => 'Something',
            'taxonomy' => 'category',
        ]);

        $this->factory->post->create([
            'post_title' => 'Germany',
            'post_category' => [$cat],
        ]);
        $this->factory->post->create([
            'post_title' => 'France',
            'post_category' => [$cat],
        ]);
        $this->factory->post->create([
            'post_title' => 'England',
            'post_category' => [$cat],
        ]);

        $posts = Timber::get_posts([
            'post_type' => 'post',
            'posts_per_page' => 2,
            'post_status' => 'publish',
            'cat' => $cat,
        ]);

        $this->assertSame(2, count($posts));
    }

    /**
     * @group wp_query_hacks
     */
    public function testGettingWithCategoryAndOtherStuff()
    {
        $pids = $this->factory->post->create_many(6);
        $cat = $this->factory->term->create([
            'name' => 'Something',
            'taxonomy' => 'category',
        ]);

        $this->factory->post->create([
            'post_title' => 'Germany',
            'post_category' => [$cat],
        ]);
        $this->factory->post->create([
            'post_title' => 'France',
            'post_category' => [$cat],
        ]);
        $this->factory->post->create([
            'post_title' => 'England',
            'post_category' => [$cat],
        ]);

        $posts = Timber::get_posts([
            'post_type' => 'post',
            'posts_per_page' => 2,
            'post_status' => 'publish',
            'category' => $cat,
        ]);

        $this->assertCount(2, $posts);
    }

    /**
     * @group wp_query_hacks
     */
    public function testGettingWithCat()
    {
        $cat = $this->factory->term->create([
            'name' => 'News',
            'taxonomy' => 'category',
        ]);

        $pids = $this->factory->post->create_many(6);
        $cats = $this->factory->post->create_many(3, [
            'post_category' => [$cat],
        ]);
        $cat_post = $this->factory->post->create([
            'post_category' => [$cat],
        ]);

        $cat_post = Timber::get_post($cat_post);
        $this->assertEquals('News', $cat_post->category()->title());

        $posts = Timber::get_posts([
            'cat' => $cat,
        ]);

        $this->assertCount(4, $posts);
    }

    public function testGettingEmptyArray()
    {
        $this->factory->post->create_many(15);

        $collection = Timber::get_posts([]);

        $this->assertEmpty($collection);
        $this->assertEquals([], $collection->to_array());
    }

    public function testFromArray()
    {
        // Create 15 posts to query by ID directly.
        $pids = $this->factory->post->create_many(15);

        // Query for our 15 posts.
        $result_ids = array_map(function ($p) {
            return $p->ID;
        }, Timber::get_posts($pids)->to_array());

        // Resulting IDs should match exactly.
        $this->assertEquals($pids, $result_ids);
    }

    public function testFromArrayWithSticky()
    {
        // Create 6 posts to query by ID directly.
        $pids = $this->factory->post->create_many(6);

        // Make one of the 6 sticky, along with a new one that will not be queried.
        update_option('sticky_posts', [$pids[0], $this->factory->post->create()]);

        // Query for our 6 posts.
        $result_ids = array_map(function ($p) {
            return $p->ID;
        }, Timber::get_posts($pids)->to_array());

        // Resulting IDs should not include the extra sticky ID.
        $this->assertEquals($pids, $result_ids);
    }

    public function testStickyAgainstQuery()
    {
        // Set up some posts. Make the second one sticky.
        $ids = [
            $this->factory->post->create([
                'post_date' => '2015-04-23 15:13:52',
            ]),
            $this->factory->post->create([
                'post_date' => '2015-04-21 15:13:52',
            ]),
            $this->factory->post->create([
                'post_date' =>
                '2015-04-24 15:13:52',
            ]),
        ];
        $sticky_id = $ids[1];
        update_option('sticky_posts', [$sticky_id]);

        $posts = Timber::get_posts([
            'post_type' => 'post',
        ]);
        $this->assertEquals($sticky_id, $posts[0]->ID);

        $query = new WP_Query('post_type=post');
        $this->assertEquals($sticky_id, $query->posts[0]->ID);
    }

    public function testGetPostsInLoop()
    {
        $posts = $this->factory->post->create_many(55);
        $this->go_to('/');

        if (have_posts()) {
            while (have_posts()) {
                the_post();
                $this->assertInstanceOf(Post::class, Timber::get_post());
            }
        }
    }

    public function testCustomPostTypeOnSinglePage()
    {
        register_post_type('job');

        // Set up the global query context for a single job post.
        $post_id = $this->factory->post->create([
            'post_type' => 'job',
        ]);
        $post = Timber::get_post($post_id);
        $this->go_to('?p=' . $post->ID);

        // Create more jobs.
        $this->factory->post->create_many(10, [
            'post_type' => 'job',
        ]);

        $jobs = Timber::get_posts([
            'post_type' => 'job',
        ]);

        $this->assertCount(10, $jobs);
    }

    /**
     * Make sure that the_post action is called when we loop over a collection of posts.
     */
    public function testThePostHook()
    {
        $this->markTestSkipped('@todo fix Timber::get_posts()');

        // Tally up the times that the_post action is called.
        $the_post_count = 0;
        add_action('the_post', function ($post) use (&$the_post_count) {
            $the_post_count++;
        });

        $this->factory->post->create_many(3);

        foreach (Timber::get_posts() as $post) {
            // whatever
        }

        $this->assertSame(3, $the_post_count);
    }

    public function testGetPostsDefault()
    {
        $this->factory->post->create_many(15);
        $this->go_to('/');

        $this->assertCount(10, Timber::get_posts());
    }

    /**
     * @expectedIncorrectUsage Timber::get_post()
     */
    public function testDeprecatedGetPostFromSlug()
    {
        $post_id = $this->factory->post->create([
            'post_name' => 'mycoolpost',
        ]);
        $this->assertNull(Timber::get_post('mycoolpost'));
    }

    /**
     * @expectedIncorrectUsage Timber::get_post()
     */
    public function testDeprecatedPostClassParameterForGetPost()
    {
        $post_id = $this->factory->post->create();
        $post = Timber\Timber::get_post($post_id, 'Deprecated class name param');

        $this->assertInstanceOf(Post::class, $post);
    }

    /**
     * @expectedIncorrectUsage Timber::get_posts()
     */
    public function testDeprecatedPostClassParameterForGetPosts()
    {
        $this->factory->post->create_many(2);

        $posts = Timber\Timber::get_posts([
            'post_type' => 'post',
        ], 'Deprecated class name param');

        $this->assertInstanceOf(Post::class, $posts[0]);
    }

    /**
     * @expectedIncorrectUsage Timber::get_posts()
     */
    public function testDeprecatedQueryStringsForGetPosts()
    {
        $this->factory->post->create_many(2);

        $posts = Timber\Timber::get_posts('post_type=post');
        $this->assertCount(2, $posts);
    }

    /**
     * @expectedIncorrectUsage Timber::get_posts()
     */
    public function testDeprecatedReturnCollectionParameterInGetPosts()
    {
        $this->factory->post->create_many(2);

        $posts = Timber\Timber::get_posts(
            [
                'post_type' => 'post',
            ],
            'Timber\Post',
            true
        );

        $this->assertEquals('Timber\Post', get_class($posts[0]));
    }

    /**
     * @expectedDeprecated Timber::query_post()
     */
    public function testDeprecatedQueryPost()
    {
        $post_id = $this->factory->post->create([
            'post_type' => 'post',
        ]);
        $post = Timber\Timber::query_post($post_id);

        $this->assertEquals($post->ID, $post_id);
    }

    /**
     * @expectedDeprecated Timber::query_posts()
     */
    public function testDeprecatedQueryPosts()
    {
        $post_ids = $this->factory->post->create_many(3, [
            'post_type' => 'post',
        ]);
        $posts = Timber\Timber::query_posts([
            'post_type' => 'post',
        ]);

        $this->assertCount(3, $posts);
    }
}
