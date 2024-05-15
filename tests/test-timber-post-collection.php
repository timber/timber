<?php

use Timber\Post;
use Timber\PostArrayObject;
use Timber\PostQuery;

require_once 'php/CollectionTestPage.php';
require_once 'php/CollectionTestPost.php';
require_once 'php/CollectionTestCustom.php';
require_once 'php/SerializablePost.php';

/**
 * @group posts-api
 * @group post-collections
 * @group pagination
 */
class TestTimberPostQuery extends Timber_UnitTestCase
{
    public function set_up()
    {
        global $wpdb;
        $wpdb->query("TRUNCATE TABLE $wpdb->posts");
        $wpdb->query("ALTER TABLE $wpdb->posts AUTO_INCREMENT = 1");
        parent::set_up();
    }

    public function testBasicCollection()
    {
        $pids = $this->factory->post->create_many(10);
        $pc = new PostQuery(new WP_Query('post_type=post&posts_per_page=6'));

        // We should be able to call count(...) directly on our collection, by virtue
        // of it implementing the Countable interface.
        $this->assertCount(6, $pc);
    }

    public function testCollectionWithWP_PostArray()
    {
        $cat = $this->factory->term->create([
            'name' => 'Things',
            'taxonomy' => 'category',
        ]);
        $pids = $this->factory->post->create_many(4, [
            'category' => $cat,
        ]);
        $posts = get_posts([
            'post_category' => [$cat],
            'posts_per_page' => 3,
        ]);
        $pc = new PostArrayObject($posts);
        $pagination = $pc->pagination();
        $this->assertNull($pagination);
    }

    public function testPaginationOnLaterPage()
    {
        $this->setPermalinkStructure('/%postname%/');
        register_post_type('portfolio');
        $pids = $this->factory->post->create_many(55, [
            'post_type' => 'portfolio',
        ]);

        // Set up the global query
        query_posts('post_type=portfolio&paged=3');

        $pagination = Timber::get_posts()->pagination();
        $this->assertCount(6, $pagination->pages);
    }

    public function testBasicCollectionWithPagination()
    {
        $pids = $this->factory->post->create_many(130);
        $page = $this->factory->post->create([
            'post_title' => 'Test',
            'post_type' => 'page',
        ]);
        $this->go_to('/');
        query_posts(['post_type=post']);
        $pc = new PostQuery(new WP_Query('post_type=post'));
        $str = Timber::compile('assets/collection-pagination.twig', [
            'posts' => $pc,
        ]);
        $str = preg_replace('/\s+/', ' ', $str);
        $this->assertEquals('<h1>POST</h1> <h1>POST</h1> <h1>POST</h1> <h1>POST</h1> <h1>POST</h1> <h1>POST</h1> <h1>POST</h1> <h1>POST</h1> <h1>POST</h1> <h1>POST</h1> <div class="l--pagination"> <div class="pagination-inner"> <div class="pagination-previous"> <span class="pagination-previous-link pagination-disabled">Previous</span> </div> <div class="pagination-pages"> <ul class="pagination-pages-list"> <li class="pagination-list-item pagination-page">1</li> <li class="pagination-list-item pagination-separator">of</li> <li class="pagination-list-item pagination-page">13</li> </ul> </div> <div class="pagination-next"> <a href="http://example.org/?paged=2" class="pagination-next-link ">Next</a> </div> </div> </div>', trim($str));
    }

    /**
     * @expectedDeprecated Timber\PostQuery::get_posts()
     */
    public function testGetPostsDeprecated()
    {
        $this->factory->post->create_many(3);

        $this->assertCount(3, Timber::get_posts([
            'post_type' => 'post',
        ])->get_posts());
    }

    public function testFoundPosts()
    {
        $this->factory->post->create_many(20);

        $query = new PostQuery(new WP_Query('post_type=post'));

        $this->assertCount(10, $query);
        $this->assertSame(20, $query->found_posts);
    }

    public function testFoundPostsInQueryWithNoFoundRows()
    {
        $this->factory->post->create_many(20);

        $query = new PostQuery(new WP_Query([
            'post_type' => 'post',
            'no_found_rows' => true,
        ]));

        $this->assertCount(10, $query);
        $this->assertSame(0, $query->found_posts);
    }

    /**
     * @ticket https://github.com/timber/timber/issues/2605
     * @return void
     */
    public function testQueryGetter()
    {
        $post_ids = $this->factory->post->create_many(2);

        $posts = Timber::get_posts([
            'post_type' => 'post',
            'has_password' => true,
            'post__in' => [$post_ids[0]],
        ]);

        $this->assertSame(true, $posts->query()->query_vars['has_password']);
        $this->assertEquals([$post_ids[0]], $posts->query()->query_vars['post__in']);
    }

    /**
     * @ticket https://github.com/timber/timber/issues/2605
     * @expectedDeprecated Timber\PostQuery::get_query()
     * @return void
     */
    public function testQueryGetterDeprecated()
    {
        $post_ids = $this->factory->post->create_many(2);

        $posts = Timber::get_posts([
            'post_type' => 'post',
            'post__in' => [$post_ids[0]],
        ]);

        $this->assertEquals([$post_ids[0]], $posts->get_query()->query_vars['post__in']);
    }

    public function testTheLoop()
    {
        foreach (range(1, 3) as $i) {
            $this->factory->post->create([
                'post_title' => 'TestPost' . $i,
                'post_date' => ('2018-09-0' . $i . ' 01:56:01'),
            ]);
        }

        $wp_query = new WP_Query('post_type=post');

        $results = Timber::compile_string(
            '{% for p in posts %}{{fn("get_the_title")}}{% endfor %}',
            [
                'posts' => new PostQuery($wp_query),
            ]
        );

        // Assert that our posts show up in reverse-chronological order.
        $this->assertEquals('TestPost3TestPost2TestPost1', $results);
    }

    public function testTwigLoopVar()
    {
        $this->factory->post->create_many(3);

        $wp_query = new WP_Query('post_type=post');

        // Dump the loop object itself each iteration, so we can see its
        // internals over time.
        $compiled = Timber::compile_string(
            "{% for p in posts %}\n{{loop|json_encode}}\n{% endfor %}\n",
            [
                'posts' => new PostQuery($wp_query),
            ]
        );

        // Get each iteration as an object (each should have its own line).
        $loop = array_map('json_decode', explode("\n", trim($compiled)));

        $this->assertSame(1, $loop[0]->index);
        $this->assertSame(2, $loop[0]->revindex0);
        $this->assertSame(3, $loop[0]->length);
        $this->assertTrue($loop[0]->first);
        $this->assertFalse($loop[0]->last);

        $this->assertSame(2, $loop[1]->index);
        $this->assertSame(1, $loop[1]->revindex0);
        $this->assertSame(3, $loop[1]->length);
        $this->assertFalse($loop[1]->first);
        $this->assertFalse($loop[1]->last);

        $this->assertSame(3, $loop[2]->index);
        $this->assertSame(0, $loop[2]->revindex0);
        $this->assertSame(3, $loop[2]->length);
        $this->assertFalse($loop[2]->first);
        $this->assertTrue($loop[2]->last);
    }

    public function testPostCount()
    {
        $posts = $this->factory->post->create_many(8);

        // We should be able to call count(...) directly on our collection, by virtue
        // of it implementing the Countable interface.
        $this->assertCount(8, new PostQuery(new WP_Query('post_type=post')));
    }

    public function testFoundPostsWithPostsPerPage()
    {
        $this->factory->post->create_many(10);

        $query = Timber::get_posts([
            'post_type' => 'post',
            'posts_per_page' => 3,
        ]);

        $this->assertCount(3, $query);
        $this->assertSame(10, $query->found_posts);
    }

    public function testArrayAccess()
    {
        // Posts are titled in reverse-chronological order.
        $this->factory->post->create([
            'post_title' => 'Post 2',
            'post_date' => '2020-01-01',
        ]);
        $this->factory->post->create([
            'post_title' => 'Post 1',
            'post_date' => '2020-01-02',
        ]);
        $this->factory->post->create([
            'post_title' => 'Post 0',
            'post_date' => '2020-01-03',
        ]);

        $posts = Timber::get_posts([
            'post_type' => 'post',
        ]);

        $this->assertEquals('Post 0', $posts[0]->title());
        $this->assertEquals('Post 1', $posts[1]->title());
        $this->assertEquals('Post 2', $posts[2]->title());
    }

    public function testIterationWithClassMaps()
    {
        // Posts are titled in reverse-chronological order.
        $this->factory->post->create([
            'post_date' => '2020-01-03',
            'post_type' => 'custom',
        ]);
        $this->factory->post->create([
            'post_date' => '2020-01-02',
            'post_type' => 'page',
        ]);
        $this->factory->post->create([
            'post_date' => '2020-01-01',
            'post_type' => 'post',
        ]);

        $this->register_post_classmap_temporarily([
            'post' => CollectionTestPost::class,
            'page' => CollectionTestPage::class,
            'custom' => CollectionTestCustom::class,
        ]);

        $query = new PostQuery(new WP_Query([
            'post_type' => ['post', 'page', 'custom'],
        ]));

        $expected = [
            CollectionTestCustom::class,
            CollectionTestPage::class,
            CollectionTestPost::class,
        ];
        foreach ($query as $idx => $post) {
            $this->assertInstanceOf($expected[$idx], $post);
        }
    }

    public function testLazyInstantiation()
    {
        // For performance reasons, we don't want to instantiate every Timber\Post instance
        // in a collection if we don't need to. We can't inspect the PostsIterator to test
        // this directly, but we can keep track of how many of each post type has been
        // instantiated via some fancy Class Map indirection.
        $postTypeCounts = [
            'post' => 0,
            'page' => 0,
        ];

        // Each time a Timber\Post is instantiated, increment the count for its post_type.
        $callback = function ($post) use (&$postTypeCounts) {
            $postTypeCounts[$post->post_type]++;
            return Post::class;
        };
        $this->add_filter_temporarily('timber/post/classmap', function () use ($callback) {
            return [
                'post' => $callback,
                'page' => $callback,
            ];
        });

        // All posts should show up before all pages in query results.
        $this->factory->post->create_many(3, [
            'post_date' => '2020-01-02',
            'post_type' => 'post',
        ]);
        $this->factory->post->create_many(3, [
            'post_date' => '2020-01-01',
            'post_type' => 'page',
        ]);

        $query = new PostQuery(new WP_Query([
            'post_type' => ['post', 'page'],
        ]));

        // No posts should have been instantiated yet.
        $this->assertEquals([
            'post' => 0,
            'page' => 0,
        ], $postTypeCounts);

        $query[0]; // post #1
        $query[1]; // post #2
        $query[2]; // post #3
        $query[3]; // page #1

        // Two of our pages should be as yet uninstantiated.
        $this->assertEquals([
            'post' => 3,
            'page' => 1,
        ], $postTypeCounts);
    }

    public function testRealize()
    {
        // For performance reasons, we don't want to instantiate every Timber\Post instance
        // in a collection if we don't need to. But sometimes we want to load them eagerly,
        // for example if .
        $postTypeCounts = [
            'post' => 0,
            'page' => 0,
        ];

        // Each time a Timber\Post is instantiated, increment the count for its post_type.
        $callback = function ($post) use (&$postTypeCounts) {
            $postTypeCounts[$post->post_type]++;
            return Post::class;
        };
        $this->add_filter_temporarily('timber/post/classmap', function () use ($callback) {
            return [
                'post' => $callback,
                'page' => $callback,
            ];
        });

        // All posts should show up before all pages in query results.
        $this->factory->post->create_many(3, [
            'post_date' => '2020-01-02',
            'post_type' => 'post',
        ]);
        $this->factory->post->create_many(3, [
            'post_date' => '2020-01-01',
            'post_type' => 'page',
        ]);

        $query = new PostQuery(new WP_Query([
            'post_type' => ['post', 'page'],
        ]));

        // Eagerly instantiate all Posts.
        $query->realize();

        // All posts should be instantiated.
        $this->assertEquals([
            'post' => 3,
            'page' => 3,
        ], $postTypeCounts);

        $query->realize();

        // Subsequent calls to realize() should be noops.
        $this->assertEquals([
            'post' => 3,
            'page' => 3,
        ], $postTypeCounts);
    }

    public function testToArray()
    {
        // Posts results are in reverse-chronological order.
        $this->factory->post->create([
            'post_date' => '2020-01-03',
            'post_type' => 'custom',
        ]);
        $this->factory->post->create([
            'post_date' => '2020-01-02',
            'post_type' => 'page',
        ]);
        $this->factory->post->create([
            'post_date' => '2020-01-01',
            'post_type' => 'post',
        ]);

        $this->add_filter_temporarily('timber/post/classmap', function () {
            return [
                'post' => CollectionTestPost::class,
                'page' => CollectionTestPage::class,
                'custom' => CollectionTestCustom::class,
            ];
        });

        $query = new PostQuery(new WP_Query([
            'post_type' => ['post', 'page', 'custom'],
        ]));

        $arr = $query->to_array();

        $this->assertInstanceOf(CollectionTestCustom::class, $arr[0]);
        $this->assertInstanceOf(CollectionTestPage::class, $arr[1]);
        $this->assertInstanceOf(CollectionTestPost::class, $arr[2]);
    }

    public function testJsonSerialize()
    {
        $this->factory->post->create([
            'post_title' => 'Tobias',
            'post_type' => 'funke',
            'meta_input' => [
                'how_many_of_us' => 'DOZENS',
            ],
        ]);

        $this->add_filter_temporarily('timber/post/classmap', function () {
            return [
                'funke' => SerializablePost::class,
            ];
        });

        $query = new PostQuery(new WP_Query('post_type=funke'));

        $this->assertEquals([
            [
                'post_title' => 'Tobias',
                'post_type' => 'funke',
                'how_many_of_us' => 'DOZENS',
            ],
        ], json_decode(json_encode($query), true));
    }
}
