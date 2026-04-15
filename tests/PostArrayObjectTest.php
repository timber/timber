<?php

namespace Timber\Tests;

use CollectionTestCustom;
use CollectionTestPage;
use CollectionTestPost;
use Mantle\Testing\Concerns\Refresh_Database;
use PHPUnit\Framework\Attributes\Group;
use SerializablePost;
use Timber\Post;
use Timber\PostArrayObject;
use Timber\Timber;
use WP_Query;

require_once __DIR__ . '/Support/CollectionTestPage.php';
require_once __DIR__ . '/Support/CollectionTestPost.php';
require_once __DIR__ . '/Support/CollectionTestCustom.php';
require_once __DIR__ . '/Support/SerializablePost.php';

#[Group('posts-api')]
#[Group('post-collections')]
class PostArrayObjectTest extends TimberIntegrationTestCase
{
    use Refresh_Database;

    public function testEmpty()
    {
        $coll = new PostArrayObject([]);

        $this->assertCount(0, $coll);
    }

    public function testCount()
    {
        static::factory()->post->create_many(20);

        $coll = new PostArrayObject(\get_posts('post_type=post&posts_per_page=-1'));

        $this->assertCount(20, $coll);
        $this->assertNull($coll->pagination());
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

        $collection = new PostArrayObject((new WP_Query([
            'post_type' => ['post', 'page'],
        ]))->posts);

        // No posts should have been instantiated yet.
        $this->assertEquals([
            'post' => 0,
            'page' => 0,
        ], $postTypeCounts);

        $collection[0]; // post #1
        $collection[1]; // post #2
        $collection[2]; // post #3
        $collection[3]; // page #1

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

        $collection = new PostArrayObject((new WP_Query([
            'post_type' => ['post', 'page'],
        ]))->posts);

        // Eagerly instantiate all Posts.
        $collection->realize();

        // All posts should be instantiated.
        $this->assertEquals([
            'post' => 3,
            'page' => 3,
        ], $postTypeCounts);

        $collection->realize();

        // Subsequent calls to realize() should be noops.
        $this->assertEquals([
            'post' => 3,
            'page' => 3,
        ], $postTypeCounts);
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

        $this->add_filter_temporarily('timber/post/classmap', fn() => [
            'post' => CollectionTestPost::class,
            'page' => CollectionTestPage::class,
            'custom' => CollectionTestCustom::class,
        ]);

        $wp_query = new WP_Query([
            'post_type' => ['post', 'page', 'custom'],
        ]);

        $collection = new PostArrayObject($wp_query->posts);

        // Test that iteration realizes the correct class.
        $expected = [
            CollectionTestCustom::class,
            CollectionTestPage::class,
            CollectionTestPost::class,
        ];
        foreach ($collection as $idx => $post) {
            $this->assertInstanceOf($expected[$idx], $post);
        }
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

        $wp_query = new WP_Query('post_type=>funke');

        $coll = new PostArrayObject($wp_query->posts);

        $this->assertEquals([
            [
                'post_title' => 'Tobias',
                'post_type' => 'funke',
                'how_many_of_us' => 'DOZENS',
            ],
        ], \json_decode(\json_encode($coll), true));
    }

    public function testPostArrayObjectTermsBasic()
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

        $wp_posts = \get_posts([
            'post__in' => [$post1, $post2, $post3],
        ]);

        $collection = new PostArrayObject($wp_posts);

        // Get all terms (merged).
        $all_terms = $collection->terms();
        $this->assertCount(3, $all_terms); // 2 categories + 1 tag

        // Verify we got the right terms.
        $term_names = \array_map(fn($term) => $term->name, \iterator_to_array($all_terms));
        $this->assertContains('News', $term_names);
        $this->assertContains('Reviews', $term_names);
        $this->assertContains('Featured', $term_names);
    }

    public function testPostArrayObjectTermsSpecificTaxonomy()
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

        $wp_posts = \get_posts([
            'post__in' => [$post1],
        ]);

        $collection = new PostArrayObject($wp_posts);

        // Get only categories.
        $categories = $collection->terms('category');
        $this->assertCount(1, $categories);
        $cat_array = \iterator_to_array($categories);
        $this->assertEquals('Category A', $cat_array[0]->name);

        // Get only tags.
        $tags = $collection->terms('post_tag');
        $this->assertCount(1, $tags);
        $tag_array = \iterator_to_array($tags);
        $this->assertEquals('Tag A', $tag_array[0]->name);
    }

    public function testPostArrayObjectTermsMultipleTaxonomies()
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

        $wp_posts = \get_posts([
            'post__in' => [$post1],
        ]);

        $collection = new PostArrayObject($wp_posts);

        // Get terms from multiple taxonomies (merged).
        $terms = $collection->terms(['category', 'post_tag']);
        $this->assertCount(2, $terms);
    }

    public function testPostArrayObjectTermsGroupedByTaxonomy()
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

        $wp_posts = \get_posts([
            'post__in' => [$post1, $post2],
        ]);

        $collection = new PostArrayObject($wp_posts);

        // Get terms grouped by taxonomy.
        $terms_by_tax = $collection->terms(['category', 'post_tag'], ['merge' => false]);

        $this->assertIsArray($terms_by_tax);
        $this->assertArrayHasKey('category', $terms_by_tax);
        $this->assertArrayHasKey('post_tag', $terms_by_tax);
        $this->assertCount(2, $terms_by_tax['category']); // News, Reviews
        $this->assertCount(2, $terms_by_tax['post_tag']); // Featured, Popular
    }

    public function testPostArrayObjectTermsWithEmptyCollection()
    {
        $collection = new PostArrayObject([]);

        $terms = $collection->terms();
        $this->assertEmpty($terms);

        // Test with merge = false.
        $terms_grouped = $collection->terms('all', ['merge' => false]);
        $this->assertIsArray($terms_grouped);
        $this->assertEmpty($terms_grouped);
    }

    public function testPostArrayObjectTermsWithMixedPostTypes()
    {
        \register_taxonomy('team', 'post');
        \register_post_type('project', [
            'public' => true,
            'taxonomies' => ['category'],
        ]);

        $cat1 = static::factory()->term->create([
            'name' => 'News',
            'taxonomy' => 'category',
        ]);
        $team1 = \wp_insert_term('Patriots', 'team');

        $post1 = static::factory()->post->create(['post_type' => 'post']);
        $project1 = static::factory()->post->create(['post_type' => 'project']);

        \wp_set_object_terms($post1, [$team1['term_id']], 'team');
        // Explicitly remove default category from post1
        \wp_set_object_terms($post1, [], 'category');
        \wp_set_object_terms($project1, [$cat1], 'category');

        $wp_posts = \array_merge(
            \get_posts(['post__in' => [$post1], 'post_type' => 'post']),
            \get_posts(['post__in' => [$project1], 'post_type' => 'project'])
        );

        $collection = new PostArrayObject($wp_posts);

        // Get all terms from all post types in the collection.
        $all_terms = $collection->terms();
        $this->assertCount(2, $all_terms); // 1 team + 1 category

        $term_names = \array_map(fn($term) => $term->name, \iterator_to_array($all_terms));
        $this->assertContains('Patriots', $term_names);
        $this->assertContains('News', $term_names);
    }
}
