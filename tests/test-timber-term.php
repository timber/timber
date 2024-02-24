<?php

use Timber\Post;

class TermTestPage extends Post
{
}

/**
* @group terms-api
*/
class TestTimberTerm extends Timber_UnitTestCase
{
    public function testTermFrom()
    {
        register_taxonomy('baseball', ['post']);
        register_taxonomy('hockey', ['post']);
        $this->factory->term->create([
            'name' => 'Rangers',
            'taxonomy' => 'baseball',
        ]);
        $this->factory->term->create([
            'name' => 'Cardinals',
            'taxonomy' => 'baseball',
        ]);
        $this->factory->term->create([
            'name' => 'Rangers',
            'taxonomy' => 'hockey',
        ]);

        $wp_terms = get_terms([
            'taxonomy' => 'baseball',
            'hide_empty' => false,
        ]);
        $baseball_teams = Timber::get_terms($wp_terms);

        $this->assertCount(2, $baseball_teams);

        $this->assertEquals('Cardinals', $baseball_teams[0]->title());
        $this->assertEquals('Rangers', $baseball_teams[1]->title());
    }

    /**
     * @ticket #2362
     */
    public function testMultiTermsWithSameSlug()
    {
        $post_tag_id = $this->factory->term->create([
            'name' => 'Security',
            'taxonomy' => 'post_tag',
        ]);
        $category_id = $this->factory->term->create([
            'name' => 'Security',
            'taxonomy' => 'category',
        ]);
        $post_id = $this->factory->post->create();
        wp_set_object_terms($post_id, $post_tag_id, 'post_tag', true);
        wp_set_object_terms($post_id, $category_id, 'category', true);

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

        register_taxonomy('baseball', ['post']);
        $term_id = $this->factory->term->create([
            'name' => 'Cardinals',
            'taxonomy' => 'baseball',
        ]);
        $post_id = $this->factory->post->create([
            'post_title' => 'Test Post',
        ]);
        $post = get_post($post_id);
        $test = Timber::get_terms($post);
    }

    public function testGetTerm()
    {
        register_taxonomy('arts', ['post']);

        $term_id = $this->factory->term->create([
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
        $term_id = $this->factory->term->create();
        $term = Timber::get_term($term_id);
        $this->assertEquals('Timber\Term', get_class($term));
    }

    public function testGetTermWithObject()
    {
        $term_id = $this->factory->term->create([
            'name' => 'Famous Commissioners',
        ]);
        $term_data = get_term($term_id, 'post_tag');
        $this->assertTrue(in_array(get_class($term_data), ['WP_Term', 'stdClass']));
        $term = Timber::get_term($term_id);
        $this->assertEquals('Famous Commissioners', $term->title());
        $this->assertEquals('Timber\Term', get_class($term));
    }

    public function testTermToString()
    {
        $term_id = $this->factory->term->create([
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
        $term_id = $this->factory->term->create([
            'name' => 'New England Patriots',
            'description' => $desc,
        ]);
        $term = Timber::get_term($term_id, 'post_tag');
        $this->assertEquals($desc, $term->description());
    }

    public function testTermInitObject()
    {
        $term_id = $this->factory->term->create();
        $term = get_term($term_id, 'post_tag');
        $term = Timber::get_term($term);
        $this->assertEquals($term->ID, $term_id);
    }

    public function testTermLink()
    {
        $term_id = $this->factory->term->create();
        $term = Timber::get_term($term_id);
        $this->assertStringContainsString('http://', $term->link());
    }

    public function testTermPath()
    {
        $term_id = $this->factory->term->create();
        $term = Timber::get_term($term_id);
        $this->assertFalse(strstr($term->path(), 'http://'));
    }

    public function testCanEdit()
    {
        $subscriber_id = $this->factory->user->create([
            'display_name' => 'Subscriber Sam',
            'user_login' => 'subsam',
            'role' => 'subscriber',
        ]);

        // Test admin role.
        wp_set_current_user(1);
        $term_id = $this->factory->term->create();
        $term = Timber::get_term($term_id);
        $this->assertTrue($term->can_edit());

        // Test subscriber role.
        wp_set_current_user($subscriber_id);
        $this->assertFalse($term->can_edit());

        wp_set_current_user(0);
    }

    /*
     * Term::posts() tests
     */

    public function testPostsDefault()
    {
        register_post_type('portfolio', [
            'taxonomies' => ['arts'],
            'public' => true,
        ]);
        register_taxonomy('arts', ['portfolio', 'post']);

        // Create a term, and some posts to assign it to.
        $term_id = $this->factory->term->create([
            'name' => 'Zong',
            'taxonomy' => 'arts',
        ]);

        // Create 12 posts total.
        // NOTE: Neither post_type has enough to satisfy the assertion below on its own,
        // but together they should exceed the default posts_per_page and we should get
        // exactly posts_per_page (10) back.
        $posts = array_merge(
            $this->factory->post->create_many(5),
            $this->factory->post->create_many(7, [
                'post_type' => 'portfolio',
            ])
        );

        // assign the term to each of our new posts
        foreach ($posts as $post_id) {
            wp_set_object_terms($post_id, $term_id, 'arts', true);
        }

        $other_id = $this->factory->term->create([
            'name' => 'Other',
            'taxonomy' => 'arts',
        ]);
        $other_posts = $this->factory->post->create_many(10);
        foreach ($other_posts as $id) {
            wp_set_object_terms($id, $other_id, 'arts', true);
        }

        $term = Timber::get_term($term_id);

        // Expect the default posts_per_page, with posts of all types.
        $this->assertCount(10, $term->posts());
        // Passing an empty array should behave exactly the same.
        $this->assertCount(10, $term->posts([]));
    }

    public function testPostsDefaultPostType()
    {
        register_post_type('portfolio', [
            'taxonomies' => ['arts'],
            'public' => true,
        ]);
        register_taxonomy('arts', ['portfolio', 'post']);

        // Create a term, and some posts to assign it to.
        $term_id = $this->factory->term->create([
            'name' => 'Zong',
            'taxonomy' => 'arts',
        ]);

        // Create 12 posts total.
        // NOTE: Neither post_type has enough to satisfy the assertion below on its own,
        // but together they should exceed the 8 we ask for so we should get exactly 8 back.
        // This is because, according to the docs, post_type defaults to "any" when using
        // tax_query.
        // https://developer.wordpress.org/reference/classes/WP_Query/parse_query/
        $posts = array_merge(
            $this->factory->post->create_many(5),
            $this->factory->post->create_many(7, [
                'post_type' => 'portfolio',
            ])
        );

        // assign the term to each of our new posts
        foreach ($posts as $post_id) {
            wp_set_object_terms($post_id, $term_id, 'arts', true);
        }

        $term = Timber::get_term($term_id);

        // Expect exactly the count we asked for.
        $this->assertCount(8, $term->posts([
            'posts_per_page' => 8,
        ]));
    }

    public function testPostsWithPostTypeQuery()
    {
        register_post_type('portfolio', [
            'taxonomies' => ['arts'],
            'public' => true,
        ]);
        register_taxonomy('arts', ['portfolio', 'post']);

        // Create a term, and some posts to assign it to.
        $term_id = $this->factory->term->create([
            'name' => 'Zong',
            'taxonomy' => 'arts',
        ]);

        // Create 12 posts total. But we should only get 7 back below even though posts_per_page
        // defaults to 10, because we limit by post_type.
        $posts = array_merge(
            $this->factory->post->create_many(5),
            $this->factory->post->create_many(7, [
                'post_type' => 'portfolio',
            ])
        );

        // assign the term to each of our new posts
        foreach ($posts as $post_id) {
            wp_set_object_terms($post_id, $term_id, 'arts', true);
        }

        $term = Timber::get_term($term_id);

        // Expect the default posts_per_page, with posts of all types.
        $this->assertCount(7, $term->posts([
            'post_type' => 'portfolio',
        ]));
    }

    public function testPostsWithTaxQuery()
    {
        register_post_type('portfolio', [
            'taxonomies' => ['arts'],
            'public' => true,
        ]);
        register_taxonomy('arts', ['portfolio', 'post']);

        // Create a term, and some posts to assign it to.
        $term_id = $this->factory->term->create([
            'taxonomy' => 'arts',
        ]);

        // Create 12 posts total.
        // NOTE: Neither post_type has enough to satisfy the assertion below on its own,
        // but together they should exceed the 8 we ask for so we should get exactly 8 back.
        // This is because, according to the docs, post_type defaults to "any" when using
        // tax_query.
        // https://developer.wordpress.org/reference/classes/WP_Query/parse_query/
        $posts = array_merge(
            $this->factory->post->create_many(5),
            $this->factory->post->create_many(7, [
                'post_type' => 'portfolio',
            ])
        );

        // assign the term to each of our new posts
        foreach ($posts as $post_id) {
            wp_set_object_terms($post_id, $term_id, 'arts', true);
        }

        // Tag one post and one portfolio with a special crafts term, too.
        register_taxonomy('crafts', ['portfolio', 'post']);
        $craft_id = $this->factory->term->create([
            'taxonomy' => 'crafts',
        ]);
        wp_set_object_terms($posts[0], $craft_id, 'crafts', true);
        wp_set_object_terms($posts[5], $craft_id, 'crafts', true);

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

    /**
     * @expectedIncorrectUsage Passing a query string to Term::posts()
     */
    public function testGetPostsWithQueryString()
    {
        register_post_type('portfolio', [
            'taxonomies' => ['post_tag'],
            'public' => true,
        ]);
        $term_id = $this->factory->term->create([
            'name' => 'Zong',
        ]);
        $this->factory->post->create_many(3, [
            'post_type' => 'post',
            'tags_input' => 'zong',
        ]);
        $this->factory->post->create_many(5, [
            'post_type' => 'portfolio',
            'tags_input' => 'zong',
        ]);

        // Count is mismatched because string-based queries default to a post_type of "post".
        $term = Timber::get_term($term_id);
        $this->assertFalse($term->posts('posts_per_page=8'));
    }

    /**
     * @expectedDeprecated Passing post_type_or_class
     * This test *partially* honors the logic described in
     * https://github.com/timber/timber/issues/799#issuecomment-192445207,
     * although that behavior is not deprecated.
     */
    public function testGetPostsWithPostTypeArg()
    {
        register_post_type('portfolio', [
            'taxonomies' => ['post_tag'],
            'public' => true,
        ]);
        $term_id = $this->factory->term->create([
            'name' => 'Zong',
        ]);
        $this->factory->post->create_many(3, [
            'post_type' => 'post',
            'tags_input' => 'zong',
        ]);
        $this->factory->post->create_many(5, [
            'post_type' => 'portfolio',
            'tags_input' => 'zong',
        ]);

        $term = Timber::get_term($term_id);
        $this->assertCount(3, $term->posts([
            'orderby' => 'menu_order',
        ], 'post'));
    }

    /**
     * @expectedIncorrectUsage Passing a post class
     */
    public function testGetPostsWithPostClassArg()
    {
        register_post_type('portfolio', [
            'taxonomies' => ['post_tag'],
            'public' => true,
        ]);
        $term_id = $this->factory->term->create([
            'name' => 'Zong',
        ]);
        $this->factory->post->create_many(3, [
            'post_type' => 'post',
            'tags_input' => 'zong',
        ]);
        $this->factory->post->create_many(5, [
            'post_type' => 'portfolio',
            'tags_input' => 'zong',
        ]);

        $term = Timber::get_term($term_id);
        $this->assertCount(3, $term->posts([
            'orderby' => 'menu_order',
        ], null, 'INCORRECT'));
    }

    /**
     * @expectedDeprecated {{ term.get_posts }}
     */
    public function testGetPostsDeprecated()
    {
        $term_id = $this->factory->term->create([
            'name' => 'Rad',
        ]);
        $posts = $this->factory->post->create_many(3, [
            'tags_input' => 'rad',
        ]);
        $term = Timber::get_term($term_id);

        $this->assertCount(3, $term->get_posts());
    }

    public function testPostsWithPostCount()
    {
        $term_id = $this->factory->term->create();
        // Assign some pages to our post_tag Term.
        $page_ids = $this->factory->post->create_many(3, [
            'post_type' => 'page',
            'post_date' => '2020-01-01',
        ]);
        // Create some posts too.
        $post_ids = $this->factory->post->create_many(3, [
            'post_date' => '2019-01-01',
        ]);
        // Tag all posts.
        foreach (array_merge($page_ids, $post_ids) as $post_id) {
            wp_set_object_terms($post_id, $term_id, 'post_tag', true);
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
        $term_id = $this->factory->term->create([
            'name' => 'Rad',
        ]);

        $posts = [
            $this->factory->post->create([
                'post_title' => 'Earlier',
                'post_date' => '2020-01-01',
                'tags_input' => 'rad',
            ]),
            $this->factory->post->create([
                'post_title' => 'Later',
                'post_date' => '2020-03-01',
                'tags_input' => 'rad',
            ]),
            $this->factory->post->create([
                'post_title' => 'Much Later',
                'post_date' => '2020-08-01',
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
        $parent_id = $this->factory->term->create([
            'name' => 'News',
            'taxonomy' => 'category',
        ]);
        $local = $this->factory->term->create([
            'name' => 'Local',
            'parent' => $parent_id,
            'taxonomy' => 'category',
        ]);
        $int = $this->factory->term->create([
            'name' => 'International',
            'parent' => $parent_id,
            'taxonomy' => 'category',
        ]);

        $term = Timber::get_term($parent_id);
        $children = $term->children();
        $this->assertSame(2, count($children));
        $this->assertEquals('Local', $children[0]->name);
    }

    /**
     * @ticket #824
     */
    public function testTermWithNativeMeta()
    {
        $tid = $this->factory->term->create([
            'name' => 'News',
            'taxonomy' => 'category',
        ]);
        add_term_meta($tid, 'foo', 'bar');
        $term = Timber::get_term($tid);
        $template = '{{term.foo}}';
        $compiled = Timber::compile_string($template, [
            'term' => $term,
        ]);
        $this->assertEquals('bar', $compiled);
    }

    /**
     * @ticket #824
     */
    public function testTermWithNativeMetaFalse()
    {
        $tid = $this->factory->term->create([
            'name' => 'News',
            'taxonomy' => 'category',
        ]);
        add_term_meta($tid, 'foo', false);
        $term = Timber::get_term($tid);
        $this->assertSame('', $term->meta('foo'));
    }

    public function testEditLink()
    {
        wp_set_current_user(1);
        $tid = $this->factory->term->create([
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
    }

    public function testWPObject()
    {
        $term_id = $this->factory->term->create();
        $term = Timber::get_term($term_id);

        $this->assertInstanceOf('WP_Term', $term->wp_object());
    }
}

class Arts extends Timber\Term
{
    public function foobar()
    {
        return 'Zebra';
    }
}
