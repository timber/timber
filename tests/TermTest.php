<?php

namespace Timber\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\Attributes\Ticket;
use Timber\Factory\TermFactory;
use Timber\Post;
use Timber\Term;
use Timber\Timber;
use WP_Term_Query;

class TermTestPage extends Post
{
}

#[Group('terms-api')]
class TermTest extends TimberIntegrationTestCase
{
    public function testTermFrom()
    {
        \register_taxonomy('baseball', ['post']);
        \register_taxonomy('hockey', ['post']);
        static::factory()->term->create([
            'name' => 'Rangers',
            'taxonomy' => 'baseball',
        ]);
        static::factory()->term->create([
            'name' => 'Cardinals',
            'taxonomy' => 'baseball',
        ]);
        static::factory()->term->create([
            'name' => 'Rangers',
            'taxonomy' => 'hockey',
        ]);

        $wp_terms = \get_terms([
            'taxonomy' => 'baseball',
            'hide_empty' => false,
        ]);
        $baseball_teams = Timber::get_terms($wp_terms);

        $this->assertCount(2, $baseball_teams);

        $this->assertEquals('Cardinals', $baseball_teams[0]->title());
        $this->assertEquals('Rangers', $baseball_teams[1]->title());
    }

    #[Ticket('#2362')]
    public function testMultiTermsWithSameSlug()
    {
        $post_tag_id = static::factory()->term->create([
            'name' => 'Security',
            'taxonomy' => 'post_tag',
        ]);
        $category_id = static::factory()->term->create([
            'name' => 'Security',
            'taxonomy' => 'category',
        ]);
        $post_id = static::factory()->post->create();
        \wp_set_object_terms($post_id, $post_tag_id, 'post_tag', true);
        \wp_set_object_terms($post_id, $category_id, 'category', true);

        $term_default = Timber::get_term_by('slug', 'security');
        $this->assertEquals('post_tag', $term_default->taxonomy);
        $this->assertEquals('Security', $term_default->title());

        $term_category = Timber::get_term_by('slug', 'security', 'category');
        $this->assertEquals('category', $term_category->taxonomy);
        $this->assertEquals('Security', $term_category->title());
    }

    public function testTermFromInvalidObject()
    {
        $this->expectException(InvalidArgumentException::class);

        \register_taxonomy('baseball', ['post']);
        $term_id = static::factory()->term->create([
            'name' => 'Cardinals',
            'taxonomy' => 'baseball',
        ]);
        $post_id = static::factory()->post->create([
            'post_title' => 'Test Post',
        ]);
        $post = \get_post($post_id);
        $test = Timber::get_terms($post);
    }

    public function testGetTerm()
    {
        \register_taxonomy('arts', ['post']);

        $term_id = static::factory()->term->create([
            'name' => 'Zong',
            'taxonomy' => 'arts',
        ]);
        $term = Timber::get_term($term_id);
        $this->assertEquals('Zong', $term->title());
        $template = '{% set zp_term = get_term("' . $term->ID . '", "arts") %}{{ zp_term.name }}';
        $string = Timber::compile_string($template);
        $this->assertEquals('Zong', $string);
    }

    public function testTerm()
    {
        $term_id = static::factory()->term->create();
        $term = Timber::get_term($term_id);
        $this->assertEquals(Term::class, $term !== null ? $term::class : self::class);
    }

    public function testGetTermWithObject()
    {
        $term_id = static::factory()->term->create([
            'name' => 'Famous Commissioners',
        ]);
        $term_data = \get_term($term_id, 'post_tag');
        $this->assertTrue(\in_array($term_data::class, ['WP_Term', 'stdClass']));
        $term = Timber::get_term($term_id);
        $this->assertEquals('Famous Commissioners', $term->title());
        $this->assertEquals(Term::class, $term !== null ? $term::class : self::class);
    }

    public function testTermToString()
    {
        $term_id = static::factory()->term->create([
            'name' => 'New England Patriots',
        ]);
        $term = Timber::get_term($term_id);
        $str = Timber::compile_string('{{term}}', [
            'term' => $term,
        ]);
        $this->assertEquals('New England Patriots', $str);
    }

    public function testTermDescription()
    {
        $desc = 'An honest football team';
        $term_id = static::factory()->term->create([
            'name' => 'New England Patriots',
            'description' => $desc,
        ]);
        $term = Timber::get_term($term_id);
        $this->assertEquals($desc, $term->description());
    }

    /**
     * Tests whether the description() method takes precedence over the description property in
     * Twig.
     *
     * @return void
     */
    public function testTermDescriptionInTwig()
    {
        $desc = '<p>An honest football team</p>';

        $term_id = static::factory()->term->create([
            'name' => 'New England Patriots',
            'description' => $desc,
        ]);

        $result = Timber::compile_string('{{ term.description }}', [
            'term' => Timber::get_term($term_id),
        ]);

        $this->assertSame(\wp_strip_all_tags($desc), $result);
    }

    public function testTermInitObject()
    {
        $term_id = static::factory()->term->create();
        $term = \get_term($term_id, 'post_tag');
        $term = Timber::get_term($term);
        $this->assertEquals($term->ID, $term_id);
    }

    public function testTermLink()
    {
        $term_id = static::factory()->term->create();
        $term = Timber::get_term($term_id);
        $this->assertStringContainsString('http://', $term->link());
    }

    public function testTermPath()
    {
        $term_id = static::factory()->term->create();
        $term = Timber::get_term($term_id);
        $this->assertFalse(\strstr($term->path(), 'http://'));
    }

    public function testCanEdit()
    {
        $admin_id = static::factory()->user->create([
            'display_name' => 'Admin User',
            'user_login' => 'adminuser',
            'role' => 'administrator',
        ]);

        $subscriber_id = static::factory()->user->create([
            'display_name' => 'Subscriber Sam',
            'user_login' => 'subsam',
            'role' => 'subscriber',
        ]);

        // Test admin role.
        \wp_set_current_user($admin_id);
        $term_id = static::factory()->term->create();
        $term = Timber::get_term($term_id);
        $this->assertTrue($term->can_edit());

        // Test subscriber role.
        \wp_set_current_user($subscriber_id);
        $this->assertFalse($term->can_edit());

        \wp_set_current_user(0);
    }

    /*
     * Term::posts() tests
     */

    public function testPostsDefault()
    {
        \register_post_type('portfolio', [
            'taxonomies' => ['arts'],
            'public' => true,
        ]);
        \register_taxonomy('arts', ['portfolio', 'post']);

        // Create a term, and some posts to assign it to.
        $term_id = static::factory()->term->create([
            'name' => 'Zong',
            'taxonomy' => 'arts',
        ]);

        // Create 12 posts total.
        // NOTE: Neither post_type has enough to satisfy the assertion below on its own,
        // but together they should exceed the default posts_per_page and we should get
        // exactly posts_per_page (10) back.
        $posts = \array_merge(
            static::factory()->post->create_many(5),
            static::factory()->post->create_many(7, [
                'post_type' => 'portfolio',
            ])
        );

        // assign the term to each of our new posts
        foreach ($posts as $post_id) {
            \wp_set_object_terms($post_id, $term_id, 'arts', true);
        }

        $other_id = static::factory()->term->create([
            'name' => 'Other',
            'taxonomy' => 'arts',
        ]);
        $other_posts = static::factory()->post->create_many(10);
        foreach ($other_posts as $id) {
            \wp_set_object_terms($id, $other_id, 'arts', true);
        }

        $term = Timber::get_term($term_id);

        // Expect the default posts_per_page, with posts of all types.
        $this->assertCount(10, $term->posts());
        // Passing an empty array should behave exactly the same.
        $this->assertCount(10, $term->posts([]));
    }

    public function testPostsDefaultPostType()
    {
        \register_post_type('portfolio', [
            'taxonomies' => ['arts'],
            'public' => true,
        ]);
        \register_taxonomy('arts', ['portfolio', 'post']);

        // Create a term, and some posts to assign it to.
        $term_id = static::factory()->term->create([
            'name' => 'Zong',
            'taxonomy' => 'arts',
        ]);

        // Create 12 posts total.
        // NOTE: Neither post_type has enough to satisfy the assertion below on its own,
        // but together they should exceed the 8 we ask for so we should get exactly 8 back.
        // This is because, according to the docs, post_type defaults to "any" when using
        // tax_query.
        // https://developer.wordpress.org/reference/classes/WP_Query/parse_query/
        $posts = \array_merge(
            static::factory()->post->create_many(5),
            static::factory()->post->create_many(7, [
                'post_type' => 'portfolio',
            ])
        );

        // assign the term to each of our new posts
        foreach ($posts as $post_id) {
            \wp_set_object_terms($post_id, $term_id, 'arts', true);
        }

        $term = Timber::get_term($term_id);

        // Expect exactly the count we asked for.
        $this->assertCount(8, $term->posts([
            'posts_per_page' => 8,
        ]));
    }

    public function testPostsWithPostTypeQuery()
    {
        \register_post_type('portfolio', [
            'taxonomies' => ['arts'],
            'public' => true,
        ]);
        \register_taxonomy('arts', ['portfolio', 'post']);

        // Create a term, and some posts to assign it to.
        $term_id = static::factory()->term->create([
            'name' => 'Zong',
            'taxonomy' => 'arts',
        ]);

        // Create 12 posts total. But we should only get 7 back below even though posts_per_page
        // defaults to 10, because we limit by post_type.
        $posts = \array_merge(
            static::factory()->post->create_many(5),
            static::factory()->post->create_many(7, [
                'post_type' => 'portfolio',
            ])
        );

        // assign the term to each of our new posts
        foreach ($posts as $post_id) {
            \wp_set_object_terms($post_id, $term_id, 'arts', true);
        }

        $term = Timber::get_term($term_id);

        // Expect the default posts_per_page, with posts of all types.
        $this->assertCount(7, $term->posts([
            'post_type' => 'portfolio',
        ]));
    }

    public function testPostsWithTaxQuery()
    {
        \register_post_type('portfolio', [
            'taxonomies' => ['arts'],
            'public' => true,
        ]);
        \register_taxonomy('arts', ['portfolio', 'post']);

        // Create a term, and some posts to assign it to.
        $term_id = static::factory()->term->create([
            'taxonomy' => 'arts',
        ]);

        // Create 12 posts total.
        // NOTE: Neither post_type has enough to satisfy the assertion below on its own,
        // but together they should exceed the 8 we ask for so we should get exactly 8 back.
        // This is because, according to the docs, post_type defaults to "any" when using
        // tax_query.
        // https://developer.wordpress.org/reference/classes/WP_Query/parse_query/
        $posts = \array_merge(
            static::factory()->post->create_many(5),
            static::factory()->post->create_many(7, [
                'post_type' => 'portfolio',
            ])
        );

        // assign the term to each of our new posts
        foreach ($posts as $post_id) {
            \wp_set_object_terms($post_id, $term_id, 'arts', true);
        }

        // Tag one post and one portfolio with a special crafts term, too.
        \register_taxonomy('crafts', ['portfolio', 'post']);
        $craft_id = static::factory()->term->create([
            'taxonomy' => 'crafts',
        ]);
        \wp_set_object_terms($posts[0], $craft_id, 'crafts', true);
        \wp_set_object_terms($posts[5], $craft_id, 'crafts', true);

        $term = Timber::get_term($term_id);

        // Expect the intersection of arts & crafts.
        $this->assertCount(2, $term->posts([
            'tax_query' => [
                [
                    'field' => 'id',
                    'terms' => $craft_id,
                    'taxonomy' => 'crafts',
                ],
                // This should get overridden; we don't want users to be able to
                // override the Term we're querying for.
                'relation' => 'OR',
            ],
        ]));
    }

    public function testGetPostsWithQueryString()
    {
        $this->setExpectedIncorrectUsage('Passing a query string to Term::posts()');
        \register_post_type('portfolio', [
            'taxonomies' => ['post_tag'],
            'public' => true,
        ]);
        $term_id = static::factory()->term->create([
            'name' => 'Zong',
        ]);
        static::factory()->post->create_many(3, [
            'post_type' => 'post',
            'tags_input' => 'zong',
        ]);
        static::factory()->post->create_many(5, [
            'post_type' => 'portfolio',
            'tags_input' => 'zong',
        ]);

        // Count is mismatched because string-based queries default to a post_type of "post".
        $term = Timber::get_term($term_id);
        $this->assertFalse($term->posts('posts_per_page=8'));
    }

    /**
     * This test *partially* honors the logic described in
     * https://github.com/timber/timber/issues/799#issuecomment-192445207,
     * although that behavior is not deprecated.
     */
    #[IgnoreDeprecations]
    public function testGetPostsWithPostTypeArg()
    {
        $this->setExpectedDeprecated('Passing post_type_or_class');
        \register_post_type('portfolio', [
            'taxonomies' => ['post_tag'],
            'public' => true,
        ]);
        $term_id = static::factory()->term->create([
            'name' => 'Zong',
        ]);
        static::factory()->post->create_many(3, [
            'post_type' => 'post',
            'tags_input' => 'zong',
        ]);
        static::factory()->post->create_many(5, [
            'post_type' => 'portfolio',
            'tags_input' => 'zong',
        ]);

        $term = Timber::get_term($term_id);
        $this->assertCount(3, $term->posts([
            'orderby' => 'menu_order',
        ], 'post'));
    }

    public function testGetPostsWithPostClassArg()
    {
        $this->setExpectedIncorrectUsage('Passing a post class');
        \register_post_type('portfolio', [
            'taxonomies' => ['post_tag'],
            'public' => true,
        ]);
        $term_id = static::factory()->term->create([
            'name' => 'Zong',
        ]);
        static::factory()->post->create_many(3, [
            'post_type' => 'post',
            'tags_input' => 'zong',
        ]);
        static::factory()->post->create_many(5, [
            'post_type' => 'portfolio',
            'tags_input' => 'zong',
        ]);

        $term = Timber::get_term($term_id);
        $this->assertCount(3, $term->posts([
            'orderby' => 'menu_order',
        ], null, 'INCORRECT'));
    }

    #[IgnoreDeprecations]
    public function testGetPostsDeprecated()
    {
        $this->setExpectedDeprecated('{{ term.get_posts }}');
        $term_id = static::factory()->term->create([
            'name' => 'Rad',
        ]);
        $posts = static::factory()->post->create_many(3, [
            'tags_input' => 'rad',
        ]);
        $term = Timber::get_term($term_id);

        $this->assertCount(3, $term->get_posts());
    }

    public function testPostsWithPostCount()
    {
        $term_id = static::factory()->term->create();
        // Assign some pages to our post_tag Term.
        $page_ids = static::factory()->post->create_many(3, [
            'post_type' => 'page',
            'post_date' => '2020-01-01 00:00:00',
        ]);
        // Create some posts too.
        $post_ids = static::factory()->post->create_many(3, [
            'post_date' => '2019-01-01 00:00:00',
        ]);
        // Tag all posts.
        foreach (\array_merge($page_ids, $post_ids) as $post_id) {
            \wp_set_object_terms($post_id, $term_id, 'post_tag', true);
        }

        $this->register_post_classmap_temporarily([
            'page' => TermTestPage::class,
        ]);

        // Get the first four posts from this term.
        $term_posts = Timber::get_term($term_id)->posts(4);

        $this->assertCount(4, $term_posts);

        // Pages should come first due to later publish dates.
        $this->assertInstanceOf(TermTestPage::class, $term_posts[0]);
        $this->assertInstanceOf(TermTestPage::class, $term_posts[1]);
        $this->assertInstanceOf(TermTestPage::class, $term_posts[2]);
        $this->assertInstanceOf(Post::class, $term_posts[3]);
    }

    public function testPostsWithExtraQueryArgs()
    {
        $term_id = static::factory()->term->create([
            'name' => 'Rad',
        ]);

        $posts = [
            static::factory()->post->create([
                'post_title' => 'Earlier',
                'post_date' => '2020-01-01 00:00:00',
                'tags_input' => 'rad',
            ]),
            static::factory()->post->create([
                'post_title' => 'Later',
                'post_date' => '2020-03-01 00:00:00',
                'tags_input' => 'rad',
            ]),
            static::factory()->post->create([
                'post_title' => 'Much Later',
                'post_date' => '2020-08-01 00:00:00',
                'tags_input' => 'rad',
            ]),
        ];

        $term = Timber::get_term($term_id);

        $term_posts = $term->posts([
            'posts_per_page' => 2,
            'orderby' => 'post_date',
            'order' => 'ASC',
        ]);

        $this->assertCount(2, $term_posts);
        $this->assertEquals('Earlier', $term_posts[0]->title());
        $this->assertEquals('Later', $term_posts[1]->title());
    }

    public function testTermChildren()
    {
        $parent_id = static::factory()->term->create([
            'name' => 'News',
            'taxonomy' => 'category',
        ]);
        $local = static::factory()->term->create([
            'name' => 'Local',
            'parent' => $parent_id,
            'taxonomy' => 'category',
        ]);
        $int = static::factory()->term->create([
            'name' => 'International',
            'parent' => $parent_id,
            'taxonomy' => 'category',
        ]);

        $term = Timber::get_term($parent_id);
        $children = $term->children();
        $this->assertSame(2, \count($children));
        $this->assertEquals('Local', $children[0]->name);
    }

    #[Ticket('#824')]
    public function testTermWithNativeMeta()
    {
        $tid = static::factory()->term->create([
            'name' => 'News',
            'taxonomy' => 'category',
        ]);
        \add_term_meta($tid, 'foo', 'bar');
        $term = Timber::get_term($tid);
        $template = '{{term.foo}}';
        $compiled = Timber::compile_string($template, [
            'term' => $term,
        ]);
        $this->assertEquals('bar', $compiled);
    }

    #[Ticket('#824')]
    public function testTermWithNativeMetaFalse()
    {
        $tid = static::factory()->term->create([
            'name' => 'News',
            'taxonomy' => 'category',
        ]);
        \add_term_meta($tid, 'foo', false);
        $term = Timber::get_term($tid);
        $this->assertSame('', $term->meta('foo'));
    }

    public function testEditLink()
    {
        $admin_id = static::factory()->user->create([
            'display_name' => 'Admin User',
            'user_login' => 'adminuser',
            'role' => 'administrator',
        ]);
        \wp_set_current_user($admin_id);

        $tid = static::factory()->term->create([
            'name' => 'News',
            'taxonomy' => 'category',
        ]);
        $term = Timber::get_term($tid);
        $links = [];

        $links[] = 'http://example.org/wp-admin/term.php?taxonomy=category&tag_ID=' . $tid . '&post_type=post';
        $links[] = 'http://example.org/wp-admin/edit-tags.php?action=edit&taxonomy=category&tag_ID=' . $tid . '&post_type=post';
        $links[] = 'http://example.org/wp-admin/edit-tags.php?action=edit&taxonomy=category&tag_ID=' . $tid;
        $links[] = 'http://example.org/wp-admin/term.php?taxonomy=category&term_id=' . $tid . '&post_type=post';
        $this->assertContains($term->edit_link(), $links);

        \wp_set_current_user(0);
    }

    public function testWPObject()
    {
        $term_id = static::factory()->term->create();
        $term = Timber::get_term($term_id);

        $this->assertInstanceOf('\WP_Term', $term->wp_object());
    }

    public function testGetTermsGroupedByTaxonomy()
    {
        // Create terms in different taxonomies.
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

        // Create posts and assign terms.
        $post1 = static::factory()->post->create();
        $post2 = static::factory()->post->create();

        \wp_set_object_terms($post1, [$cat1, $cat2], 'category');
        \wp_set_object_terms($post1, [$tag1], 'post_tag');
        \wp_set_object_terms($post2, [$cat1], 'category');
        \wp_set_object_terms($post2, [$tag2], 'post_tag');

        // Get terms grouped by taxonomy using merge: false.
        $terms_by_tax = Timber::get_terms([
            'taxonomy' => ['category', 'post_tag'],
            'object_ids' => [$post1, $post2],
        ], [
            'merge' => false,
        ]);

        $this->assertIsArray($terms_by_tax);
        $this->assertArrayHasKey('category', $terms_by_tax);
        $this->assertArrayHasKey('post_tag', $terms_by_tax);
        $this->assertCount(2, $terms_by_tax['category']); // News, Reviews
        $this->assertCount(2, $terms_by_tax['post_tag']); // Featured, Popular

        // Verify term names.
        $cat_names = \array_map(fn ($term) => $term->name, $terms_by_tax['category']);
        $tag_names = \array_map(fn ($term) => $term->name, $terms_by_tax['post_tag']);

        $this->assertContains('News', $cat_names);
        $this->assertContains('Reviews', $cat_names);
        $this->assertContains('Featured', $tag_names);
        $this->assertContains('Popular', $tag_names);
    }

    public function testGetTermsMergedByDefault()
    {
        // Create terms in different taxonomies.
        $cat1 = static::factory()->term->create([
            'name' => 'News',
            'taxonomy' => 'category',
        ]);
        $tag1 = static::factory()->term->create([
            'name' => 'Featured',
            'taxonomy' => 'post_tag',
        ]);

        $post = static::factory()->post->create();
        \wp_set_object_terms($post, $cat1, 'category');
        \wp_set_object_terms($post, $tag1, 'post_tag');

        // Get terms merged (default behavior).
        $terms = Timber::get_terms([
            'taxonomy' => ['category', 'post_tag'],
            'object_ids' => [$post],
        ]);

        // Should be a flat array of terms, not grouped by taxonomy.
        $this->assertCount(2, $terms);
        $this->assertIsArray($terms);
        // The array should not have taxonomy keys.
        $this->assertArrayNotHasKey('category', $terms);
        $this->assertArrayNotHasKey('post_tag', $terms);
    }

    public function testGetTermsMergeFalseWithSingleTaxonomy()
    {
        // merge: false with a single taxonomy should return a flat array.
        $cat1 = static::factory()->term->create([
            'name' => 'News',
            'taxonomy' => 'category',
        ]);
        $cat2 = static::factory()->term->create([
            'name' => 'Reviews',
            'taxonomy' => 'category',
        ]);

        $post = static::factory()->post->create();
        \wp_set_object_terms($post, [$cat1, $cat2], 'category');

        $terms = Timber::get_terms([
            'taxonomy' => ['category'],
            'object_ids' => [$post],
        ], [
            'merge' => false,
        ]);

        // Only one taxonomy – should return a flat array, not grouped.
        $this->assertIsArray($terms);
        $this->assertArrayNotHasKey('category', $terms);
        $this->assertCount(2, $terms);
        foreach ($terms as $term) {
            $this->assertInstanceOf(Term::class, $term);
        }
    }

    public function testGetTermsMergeFalseWithTaxonomyOrderPreserved()
    {
        // Result keys should follow the order defined in params['taxonomy'].
        \register_taxonomy('brands', ['post']);

        $cat = static::factory()->term->create([
            'name' => 'Tech',
            'taxonomy' => 'category',
        ]);
        $tag = static::factory()->term->create([
            'name' => 'Hot',
            'taxonomy' => 'post_tag',
        ]);
        $brand = static::factory()->term->create([
            'name' => 'Acme',
            'taxonomy' => 'brands',
        ]);

        $post = static::factory()->post->create();
        \wp_set_object_terms($post, $cat, 'category');
        \wp_set_object_terms($post, $tag, 'post_tag');
        \wp_set_object_terms($post, $brand, 'brands');

        $grouped = Timber::get_terms([
            'taxonomy' => ['brands', 'post_tag', 'category'],
            'object_ids' => [$post],
        ], [
            'merge' => false,
        ]);

        $this->assertSame(['brands', 'post_tag', 'category'], \array_keys($grouped));
    }

    public function testGetTermsMergeFalseWithWPTermQuery()
    {
        // merge: false should work when a WP_Term_Query object is passed directly.
        $cat = static::factory()->term->create([
            'name' => 'Alpha',
            'taxonomy' => 'category',
        ]);
        $tag = static::factory()->term->create([
            'name' => 'Beta',
            'taxonomy' => 'post_tag',
        ]);

        $post = static::factory()->post->create();
        \wp_set_object_terms($post, $cat, 'category');
        \wp_set_object_terms($post, $tag, 'post_tag');

        $query = new WP_Term_Query([
            'taxonomy' => ['category', 'post_tag'],
            'object_ids' => [$post],
        ]);

        $factory = new TermFactory();
        $grouped = $factory->from($query, [
            'merge' => false,
        ]);

        $this->assertIsArray($grouped);
        $this->assertArrayHasKey('category', $grouped);
        $this->assertArrayHasKey('post_tag', $grouped);
    }

    public function testGetTermsMergeTrueExplicit()
    {
        // Passing merge: true explicitly should behave identically to the default.
        $cat = static::factory()->term->create([
            'name' => 'Sport',
            'taxonomy' => 'category',
        ]);
        $tag = static::factory()->term->create([
            'name' => 'Trending',
            'taxonomy' => 'post_tag',
        ]);

        $post = static::factory()->post->create();
        \wp_set_object_terms($post, $cat, 'category');
        \wp_set_object_terms($post, $tag, 'post_tag');

        $terms = Timber::get_terms([
            'taxonomy' => ['category', 'post_tag'],
            'object_ids' => [$post],
        ], [
            'merge' => true,
        ]);

        $this->assertCount(2, $terms);
        $this->assertArrayNotHasKey('category', $terms);
        $this->assertArrayNotHasKey('post_tag', $terms);
    }

    public function testGetTermsMergeWithArrayInvocation()
    {
        // Test that passing an array of term IDs with merge: false returns a flat array.
        $term_ids = [
            static::factory()->term->create([
                'name' => 'Term 1',
                'taxonomy' => 'category',
            ]),
            static::factory()->term->create([
                'name' => 'Term 2',
                'taxonomy' => 'post_tag',
            ]),
        ];

        $terms = Timber::get_terms($term_ids, [
            'merge' => false,
        ]);

        // Should return a flat array of terms, not grouped by taxonomy.
        $this->assertCount(2, $terms);
        $this->assertArrayNotHasKey('category', $terms);
        $this->assertArrayNotHasKey('post_tag', $terms);
    }
}

class Arts extends Term
{
    public function foobar()
    {
        return 'Zebra';
    }
}
