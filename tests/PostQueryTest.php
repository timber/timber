<?php

namespace Timber\Tests;

use CollectionTestCustom;
use CollectionTestPage;
use CollectionTestPost;
use Mantle\Testing\Attributes\PermalinkStructure;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\Attributes\Ticket;
use SerializablePost;
use Timber\Post;
use Timber\PostArrayObject;
use Timber\PostQuery;
use Timber\Timber;
use WP_Query;

require_once __DIR__ . '/Support/CollectionTestPage.php';
require_once __DIR__ . '/Support/CollectionTestPost.php';
require_once __DIR__ . '/Support/CollectionTestCustom.php';
require_once __DIR__ . '/Support/SerializablePost.php';

#[Group('posts-api')]
#[Group('post-collections')]
#[Group('pagination')]
class PostQueryTest extends TimberIntegrationTestCase
{
    public function testBasicCollection()
    {
        $pids = static::factory()->post->create_many(10);
        $pc = new PostQuery(new WP_Query('post_type=post&posts_per_page=6'));

        // We should be able to call count(...) directly on our collection, by virtue
        // of it implementing the Countable interface.
        $this->assertCount(6, $pc);
    }

    public function testCollectionWithWP_PostArray()
    {
        $cat = static::factory()->term->create([
            'name' => 'Things',
            'taxonomy' => 'category',
        ]);
        $pids = static::factory()->post->create_many(4, [
            'category' => $cat,
        ]);
        $posts = \get_posts([
            'post_category' => [$cat],
            'posts_per_page' => 3,
        ]);
        $pc = new PostArrayObject($posts);
        $pagination = $pc->pagination();
        $this->assertNull($pagination);
    }

    #[PermalinkStructure('/%postname%/')]
    public function testPaginationOnLaterPage()
    {
        \register_post_type('portfolio');
        $pids = static::factory()->post->create_many(55, [
            'post_type' => 'portfolio',
        ]);

        // Set up the global query
        \query_posts('post_type=portfolio&paged=3');

        $pagination = Timber::get_posts()->pagination();
        $this->assertCount(6, $pagination->pages);
    }

    public function testBasicCollectionWithPagination()
    {
        $pids = static::factory()->post->create_many(130);
        $page = static::factory()->post->create([
            'post_title' => 'Test',
            'post_type' => 'page',
        ]);
        $this->get('/');
        $pc = new PostQuery(new WP_Query('post_type=post'));
        $str = Timber::compile('assets/collection-pagination.twig', [
            'posts' => $pc,
        ]);
        $str = \preg_replace('/\s+/', ' ', $str);

        // Test for pagination link structure - URL may vary depending on permalink settings
        $this->assertStringContainsString('<h1>POST</h1> <h1>POST</h1> <h1>POST</h1> <h1>POST</h1> <h1>POST</h1>', \trim((string) $str));
        $this->assertStringContainsString('<div class="l--pagination">', $str);
        $this->assertStringContainsString('pagination-page">1</li>', $str);
        $this->assertStringContainsString('pagination-page">13</li>', $str);
        $this->assertStringContainsString('pagination-next-link', $str);
    }

    #[IgnoreDeprecations]
    public function testGetPostsDeprecated()
    {
        $this->setExpectedDeprecated('Timber\PostQuery::get_posts()');
        static::factory()->post->create_many(3);

        $this->assertCount(3, Timber::get_posts([
            'post_type' => 'post',
        ])->get_posts());
    }

    public function testFoundPosts()
    {
        $post_ids = static::factory()->post->create_many(20);

        $query = new PostQuery(new WP_Query([
            'post_type' => 'post',
            'post__in' => $post_ids,
            'orderby' => 'post__in',
        ]));

        $this->assertCount(10, $query);
        $this->assertSame(20, $query->found_posts);
    }

    public function testFoundPostsInQueryWithNoFoundRows()
    {
        $post_ids = static::factory()->post->create_many(20);

        $query = new PostQuery(new WP_Query([
            'post_type' => 'post',
            'post__in' => $post_ids,
            'no_found_rows' => true,
        ]));

        $this->assertCount(10, $query);
        $this->assertSame(0, $query->found_posts);
    }

    /**
     * @return void
     */
    #[Ticket('https://github.com/timber/timber/issues/2605')]
    public function testQueryGetter()
    {
        $post_ids = static::factory()->post->create_many(2);

        $posts = Timber::get_posts([
            'post_type' => 'post',
            'has_password' => true,
            'post__in' => [$post_ids[0]],
        ]);

        $this->assertSame(true, $posts->query()->query_vars['has_password']);
        $this->assertEquals([$post_ids[0]], $posts->query()->query_vars['post__in']);
    }

    #[Ticket('https://github.com/timber/timber/issues/2605')]
    #[IgnoreDeprecations]
    public function testQueryGetterDeprecated()
    {
        $this->setExpectedDeprecated('Timber\PostQuery::get_query()');
        $post_ids = static::factory()->post->create_many(2);

        $posts = Timber::get_posts([
            'post_type' => 'post',
            'post__in' => [$post_ids[0]],
        ]);

        $this->assertEquals([$post_ids[0]], $posts->get_query()->query_vars['post__in']);
    }

    public function testTheLoop()
    {
        $post_ids = [];
        foreach (\range(1, 3) as $i) {
            $post_ids[] = static::factory()->post->create([
                'post_title' => 'TestPost' . $i,
                'post_date' => ('2018-09-0' . $i . ' 01:56:01'),
            ]);
        }

        $wp_query = new WP_Query([
            'post_type' => 'post',
            'post__in' => $post_ids,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

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
        $post_ids = static::factory()->post->create_many(3);

        $wp_query = new WP_Query([
            'post_type' => 'post',
            'post__in' => $post_ids,
            'orderby' => 'post__in',
        ]);

        // Dump the loop object itself each iteration, so we can see its
        // internals over time.
        $compiled = Timber::compile_string(
            "{% for p in posts %}\n{{loop|json_encode}}\n{% endfor %}\n",
            [
                'posts' => new PostQuery($wp_query),
            ]
        );

        // Get each iteration as an object (each should have its own line).
        $loop = \array_map(json_decode(...), \explode("\n", \trim($compiled)));

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
        $post_ids = static::factory()->post->create_many(8);

        // We should be able to call count(...) directly on our collection, by virtue
        // of it implementing the Countable interface.
        $this->assertCount(8, new PostQuery(new WP_Query([
            'post_type' => 'post',
            'post__in' => $post_ids,
            'orderby' => 'post__in',
        ])));
    }

    public function testFoundPostsWithPostsPerPage()
    {
        $post_ids = static::factory()->post->create_many(10);

        $query = Timber::get_posts([
            'post_type' => 'post',
            'post__in' => $post_ids,
            'posts_per_page' => 3,
        ]);

        $this->assertCount(3, $query);
        $this->assertSame(10, $query->found_posts);
    }

    public function testArrayAccess()
    {
        // Posts are titled in reverse-chronological order.
        static::factory()->post->create([
            'post_title' => 'Post 2',
            'post_date' => '2020-01-01 00:00:00',
        ]);
        static::factory()->post->create([
            'post_title' => 'Post 1',
            'post_date' => '2020-01-02 00:00:00',
        ]);
        static::factory()->post->create([
            'post_title' => 'Post 0',
            'post_date' => '2020-01-03 00:00:00',
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
        static::factory()->post->create([
            'post_date' => '2020-01-03 00:00:00',
            'post_type' => 'custom',
        ]);
        static::factory()->post->create([
            'post_date' => '2020-01-02 00:00:00',
            'post_type' => 'page',
        ]);
        static::factory()->post->create([
            'post_date' => '2020-01-01 00:00:00',
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
        // For performance reasons, we don't want to instantiate every \Timber\Post instance
        // in a collection if we don't need to. We can't inspect the PostsIterator to test
        // this directly, but we can keep track of how many of each post type has been
        // instantiated via some fancy Class Map indirection.
        $postTypeCounts = [
            'post' => 0,
            'page' => 0,
        ];

        // Each time a \Timber\Post is instantiated, increment the count for its post_type.
        $callback = function ($post) use (&$postTypeCounts) {
            $postTypeCounts[$post->post_type]++;
            return Post::class;
        };
        $this->add_filter_temporarily('timber/post/classmap', fn() => [
            'post' => $callback,
            'page' => $callback,
        ]);

        // All posts should show up before all pages in query results.
        static::factory()->post->create_many(3, [
            'post_date' => '2020-01-02 00:00:00',
            'post_type' => 'post',
        ]);
        static::factory()->post->create_many(3, [
            'post_date' => '2020-01-01 00:00:00',
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
        // For performance reasons, we don't want to instantiate every \Timber\Post instance
        // in a collection if we don't need to. But sometimes we want to load them eagerly,
        // for example if .
        $postTypeCounts = [
            'post' => 0,
            'page' => 0,
        ];

        // Each time a \Timber\Post is instantiated, increment the count for its post_type.
        $callback = function ($post) use (&$postTypeCounts) {
            $postTypeCounts[$post->post_type]++;
            return Post::class;
        };
        $this->add_filter_temporarily('timber/post/classmap', fn() => [
            'post' => $callback,
            'page' => $callback,
        ]);

        // All posts should show up before all pages in query results.
        static::factory()->post->create_many(3, [
            'post_date' => '2020-01-02 00:00:00',
            'post_type' => 'post',
        ]);
        static::factory()->post->create_many(3, [
            'post_date' => '2020-01-01 00:00:00',
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
        static::factory()->post->create([
            'post_date' => '2020-01-03 00:00:00',
            'post_type' => 'custom',
        ]);
        static::factory()->post->create([
            'post_date' => '2020-01-02 00:00:00',
            'post_type' => 'page',
        ]);
        static::factory()->post->create([
            'post_date' => '2020-01-01 00:00:00',
            'post_type' => 'post',
        ]);

        $this->add_filter_temporarily('timber/post/classmap', fn() => [
            'post' => CollectionTestPost::class,
            'page' => CollectionTestPage::class,
            'custom' => CollectionTestCustom::class,
        ]);

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
        static::factory()->post->create([
            'post_title' => 'Tobias',
            'post_type' => 'funke',
            'meta_input' => [
                'how_many_of_us' => 'DOZENS',
            ],
        ]);

        $this->add_filter_temporarily('timber/post/classmap', fn() => [
            'funke' => SerializablePost::class,
        ]);

        $query = new PostQuery(new WP_Query('post_type=funke'));

        $this->assertEquals([
            [
                'post_title' => 'Tobias',
                'post_type' => 'funke',
                'how_many_of_us' => 'DOZENS',
            ],
        ], \json_decode(\json_encode($query), true));
    }

    public function testPostQueryTermsBasic()
    {
        $cat1 = static::factory()->term->create([
            'name' => 'News',
            'taxonomy' => 'category',
        ]);
        $cat2 = static::factory()->term->create([
            'name' => 'Reviews',
            'taxonomy' => 'category',
        ]);
        $tag1 = static::factory()->term->create([
            'name' => 'Featured',
            'taxonomy' => 'post_tag',
        ]);

        $post1 = static::factory()->post->create();
        $post2 = static::factory()->post->create();
        $post3 = static::factory()->post->create();

        \wp_set_object_terms($post1, [$cat1, $tag1], 'category');
        \wp_set_object_terms($post1, [$tag1], 'post_tag');
        \wp_set_object_terms($post2, [$cat2], 'category');
        \wp_set_object_terms($post3, [$cat1], 'category');

        $query = new PostQuery(new WP_Query([
            'post_type' => 'post',
            'post__in' => [$post1, $post2, $post3],
        ]));

        // Get all terms (merged).
        $all_terms = $query->terms();
        $this->assertCount(3, $all_terms); // 2 categories + 1 tag

        // Verify we got the right terms.
        $term_names = \array_map(fn($term) => $term->name, \iterator_to_array($all_terms));
        $this->assertContains('News', $term_names);
        $this->assertContains('Reviews', $term_names);
        $this->assertContains('Featured', $term_names);
    }

    public function testPostQueryTermsSpecificTaxonomy()
    {
        $cat1 = static::factory()->term->create([
            'name' => 'Category A',
            'taxonomy' => 'category',
        ]);
        $tag1 = static::factory()->term->create([
            'name' => 'Tag A',
            'taxonomy' => 'post_tag',
        ]);

        $post1 = static::factory()->post->create();
        \wp_set_object_terms($post1, [$cat1], 'category');
        \wp_set_object_terms($post1, [$tag1], 'post_tag');

        $query = new PostQuery(new WP_Query([
            'post_type' => 'post',
            'post__in' => [$post1],
        ]));

        // Get only categories.
        $categories = $query->terms('category');
        $this->assertCount(1, $categories);
        $cat_array = \iterator_to_array($categories);
        $this->assertEquals('Category A', $cat_array[0]->name);

        // Get only tags.
        $tags = $query->terms('post_tag');
        $this->assertCount(1, $tags);
        $tag_array = \iterator_to_array($tags);
        $this->assertEquals('Tag A', $tag_array[0]->name);
    }

    public function testPostQueryTermsMultipleTaxonomies()
    {
        \register_taxonomy('project_type', 'post');

        $cat1 = static::factory()->term->create([
            'name' => 'Cat 1',
            'taxonomy' => 'category',
        ]);
        $tag1 = static::factory()->term->create([
            'name' => 'Tag 1',
            'taxonomy' => 'post_tag',
        ]);
        $type1 = \wp_insert_term('Type 1', 'project_type');

        $post1 = static::factory()->post->create();
        \wp_set_object_terms($post1, [$cat1], 'category');
        \wp_set_object_terms($post1, [$tag1], 'post_tag');
        \wp_set_object_terms($post1, [$type1['term_id']], 'project_type');

        $query = new PostQuery(new WP_Query([
            'post_type' => 'post',
            'post__in' => [$post1],
        ]));

        // Get terms from multiple taxonomies (merged).
        $terms = $query->terms(['category', 'post_tag']);
        $this->assertCount(2, $terms);
    }

    public function testPostQueryTermsGroupedByTaxonomy()
    {
        $cat1 = static::factory()->term->create([
            'name' => 'News',
            'taxonomy' => 'category',
        ]);
        $cat2 = static::factory()->term->create([
            'name' => 'Reviews',
            'taxonomy' => 'category',
        ]);
        $tag1 = static::factory()->term->create([
            'name' => 'Featured',
            'taxonomy' => 'post_tag',
        ]);
        $tag2 = static::factory()->term->create([
            'name' => 'Popular',
            'taxonomy' => 'post_tag',
        ]);

        $post1 = static::factory()->post->create();
        $post2 = static::factory()->post->create();

        \wp_set_object_terms($post1, [$cat1, $cat2], 'category');
        \wp_set_object_terms($post1, [$tag1], 'post_tag');
        \wp_set_object_terms($post2, [$cat1], 'category');
        \wp_set_object_terms($post2, [$tag2], 'post_tag');

        $query = new PostQuery(new WP_Query([
            'post_type' => 'post',
            'post__in' => [$post1, $post2],
        ]));

        // Get terms grouped by taxonomy.
        $terms_by_tax = $query->terms(['category', 'post_tag'], ['merge' => false]);

        $this->assertIsArray($terms_by_tax);
        $this->assertArrayHasKey('category', $terms_by_tax);
        $this->assertArrayHasKey('post_tag', $terms_by_tax);
        $this->assertCount(2, $terms_by_tax['category']); // News, Reviews
        $this->assertCount(2, $terms_by_tax['post_tag']); // Featured, Popular
    }

    public function testPostQueryTermsWithEmptyQuery()
    {
        $query = new PostQuery(new WP_Query([
            'post_type' => 'post',
            'post__in' => [999999], // Non-existent post
        ]));

        $terms = $query->terms();
        $this->assertEmpty($terms);

        // Test with merge = false.
        $terms_grouped = $query->terms('all', ['merge' => false]);
        $this->assertIsArray($terms_grouped);
        $this->assertEmpty($terms_grouped);
    }

    public function testPostQueryTermsWithCustomTaxonomy()
    {
        \register_taxonomy('team', 'post');

        $team1 = \wp_insert_term('Patriots', 'team');
        $team2 = \wp_insert_term('Bills', 'team');

        $post1 = static::factory()->post->create();
        $post2 = static::factory()->post->create();

        \wp_set_object_terms($post1, [$team1['term_id']], 'team');
        \wp_set_object_terms($post2, [$team2['term_id']], 'team');

        $query = new PostQuery(new WP_Query([
            'post_type' => 'post',
            'post__in' => [$post1, $post2],
        ]));

        $teams = $query->terms('team');
        $this->assertCount(2, $teams);

        $team_names = \array_map(fn($term) => $term->name, \iterator_to_array($teams));
        $this->assertContains('Patriots', $team_names);
        $this->assertContains('Bills', $team_names);
    }

    public function testPostQueryTermsUniqueness()
    {
        $cat1 = static::factory()->term->create([
            'name' => 'Shared Category',
            'taxonomy' => 'category',
        ]);

        // Create multiple posts with the same category.
        $post1 = static::factory()->post->create();
        $post2 = static::factory()->post->create();
        $post3 = static::factory()->post->create();

        \wp_set_object_terms($post1, [$cat1], 'category');
        \wp_set_object_terms($post2, [$cat1], 'category');
        \wp_set_object_terms($post3, [$cat1], 'category');

        $query = new PostQuery(new WP_Query([
            'post_type' => 'post',
            'post__in' => [$post1, $post2, $post3],
        ]));

        $terms = $query->terms('category');
        // Should only return 1 term even though 3 posts share it.
        $this->assertCount(1, $terms);

        $term_array = \iterator_to_array($terms);
        $this->assertEquals('Shared Category', $term_array[0]->name);
    }

    public function testPostQueryTermsWithQueryArgs()
    {
        $cat1 = static::factory()->term->create([
            'name' => 'Zebra',
            'taxonomy' => 'category',
        ]);
        $cat2 = static::factory()->term->create([
            'name' => 'Apple',
            'taxonomy' => 'category',
        ]);
        $cat3 = static::factory()->term->create([
            'name' => 'Banana',
            'taxonomy' => 'category',
        ]);

        $post1 = static::factory()->post->create();
        \wp_set_object_terms($post1, [$cat1, $cat2, $cat3], 'category');

        $query = new PostQuery(new WP_Query([
            'post_type' => 'post',
            'post__in' => [$post1],
        ]));

        // Test ordering.
        $terms = $query->terms([
            'taxonomy' => 'category',
            'orderby' => 'name',
            'order' => 'ASC',
        ]);

        $term_names = \array_map(fn($term) => $term->name, \iterator_to_array($terms));
        $this->assertEquals(['Apple', 'Banana', 'Zebra'], $term_names);
    }
}
