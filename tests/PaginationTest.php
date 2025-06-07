<?php

namespace Timber\Tests;

use Mantle\Testing\Attributes\PermalinkStructure;
use Mantle\Testing\Concerns\Refresh_Database;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Ticket;
use Timber\PostQuery;
use Timber\Timber;
use WP_Query;

#[PermalinkStructure('')]
#[Group('posts-api')]
#[Group('post-collections')]
#[Group('pagination')]
class PaginationTest extends TimberIntegrationTestCase
{
    use Refresh_Database;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        \register_post_type('portfolio', [
            'public' => true,
            'has_archive' => true,
        ]);

        \register_post_type('my_cpt', [
            'public' => true,
            'has_archive' => true,
        ]);
    }

    public function testPaginationWithPostQuery()
    {
        $pids = static::factory()->post->create_many(33);
        $pids = static::factory()->post->create_many(55, [
            'post_type' => 'portfolio',
        ]);
        $this->get(\home_url('/'));

        $query = Timber::get_posts([
            'post_type' => 'portfolio',
        ]);

        $this->assertCount(6, $query->pagination()->pages);
    }

    public function testDoubleEncodedPaginationUrl()
    {
        static::factory()->post->create_many(33, [
            'post_type' => 'portfolio',
        ]);
        $this->get(\home_url('/portfolio/page/3?wx9um%2522%253e%253cscript%253ealert%25281%2529%253c%252fscript%
253eaq86s=1'));

        $link = Timber::compile_string("{{ posts.pagination.next.link|e('esc_url') }}", [
            'posts' => new PostQuery($GLOBALS['wp_query']),
        ]);
        $this->assertEquals('http://example.org/portfolio/page/4/?wx9umscriptalert(1)/script%_253eaq86s=1', $link);
    }

    #[PermalinkStructure('/%postname%/')]
    public function testDoubleEncodedPaginationUrlWithEscHTML()
    {
        static::factory()->post->create_many(33, [
            'post_type' => 'portfolio',
        ]);
        $this->get(\home_url('/portfolio/page/3?wx9um%2522%253e%253cscript%253ealert%25281%2529%253c%252fscript%
253eaq86s=1'));

        $link = Timber::compile_string("{{ posts.pagination.next.link|e('esc_html') }}", [
            'posts' => new PostQuery($GLOBALS['wp_query']),
        ]);
        $this->assertEquals('http://example.org/portfolio/page/4/?wx9umscriptalert(1)/script%_253eaq86s=1', $link);
    }

    public function testPaginationInCategory()
    {
        static::factory()->post->create_many(73);

        $news_id = static::factory()->term->create([
            'name' => 'News',
            'taxonomy' => 'category',
        ]);
        $posts = static::factory()->post->create_many(31);
        foreach ($posts as $post) {
            \wp_set_object_terms($post, $news_id, 'category');
        }

        $this->get(\home_url('?cat=' . $news_id));

        // Let Timber fall back on the main query.
        $pagination = Timber::get_posts()->pagination();

        $this->assertCount(4, $pagination->pages);
    }

    // tests for pagination object set on PostCollection

    public function testPostsCollectionPagination()
    {
        static::factory()->post->create_many(13);
        $pagination = Timber::get_posts([
            'post_type' => 'post',
        ])->pagination();

        $this->assertCount(2, $pagination->pages);
    }

    #[PermalinkStructure('')]
    public function testCollectionPaginationSearch()
    {
        static::factory()->post->create_many(55, [
            'post_title' => 'searchable post',
        ]);
        $this->get(\home_url('?s=post'));
        $posts = new PostQuery($GLOBALS['wp_query']);
        $pagination = $posts->pagination();

        $this->assertEquals(\home_url() . \esc_url('/?paged=5&s=post'), $pagination->pages[4]['link']);
    }

    public function testCollectionPaginationOnLaterPage()
    {
        static::factory()->post->create_many(55, [
            'post_type' => 'portfolio',
        ]);
        $this->get(\home_url('/portfolio/page/3'));
        $posts = new PostQuery(new WP_Query('post_type=portfolio&paged=3'));
        $pagination = $posts->pagination();

        $this->assertSame(6, \count($pagination->pages));
    }

    #[PermalinkStructure('/%postname%/')]
    public function testCollectionPaginationWithSize()
    {
        static::factory()->post->create_many(99, [
            'post_type' => 'portfolio',
        ]);
        $posts = new PostQuery(new WP_Query('post_type=portfolio&posts_per_page=20'));
        $pagination = $posts->pagination();

        $this->assertSame(5, \count($pagination->pages));
    }

    #[PermalinkStructure('/%postname%/')]
    public function testCollectionPaginationSearchPrettyWithPostname()
    {
        static::factory()->post->create_many(55, [
            'post_title' => 'searchable post',
        ]);
        $archive = \home_url('?s=post');
        $this->get($archive);
        $posts = new PostQuery(new WP_Query('s=post'));
        $pagination = $posts->pagination();

        $this->assertEquals('http://example.org/page/5/?s=post', $pagination->pages[4]['link']);
    }

    #[PermalinkStructure('/%postname%/')]
    public function testCollectionPaginationSearchPrettyWithPostnameNext()
    {
        static::factory()->post->create_many(55, [
            'post_title' => 'searchable post',
        ]);
        $archive = \home_url('?s=post');
        $this->get($archive);
        $posts = new PostQuery(new WP_Query('s=post'));
        $pagination = $posts->pagination();

        $this->assertEquals('http://example.org/page/2/?s=post', $pagination->next['link']);
    }

    #[PermalinkStructure('/%postname%/')]
    public function testCollectionPaginationQueryVars()
    {
        global $wp;
        $wp->add_query_var('myvar');
        static::factory()->post->create_many(55);
        $this->get(\home_url('?myvar=value'));
        $posts = new PostQuery($GLOBALS['wp_query']);
        $pagination = $posts->pagination();

        $this->assertEquals('http://example.org/page/2/?myvar=value', $pagination->next['link']);
    }

    #[PermalinkStructure('/%postname%/')]
    public function testCollectionPaginationSearchPrettyWithPostnamePrev()
    {
        static::factory()->post->create_many(55, [
            'post_title' => 'searchable thing',
        ]);
        $archive = \home_url('page/4/?s=thing');
        $this->get($archive);
        $posts = new PostQuery(new WP_Query('s=thing&paged=4'));
        $pagination = $posts->pagination();

        $this->assertEquals('http://example.org/page/3/?s=thing', $pagination->prev['link']);
    }

    #[PermalinkStructure('/blog/%year%/%monthnum%/%postname%/')]
    public function testCollectionPaginationSearchPretty()
    {
        static::factory()->post->create_many(55, [
            'post_title' => 'searchable elephant',
        ]);
        $archive = \home_url('?s=elephant');
        $this->get($archive);
        $posts = Timber::get_posts();
        $pagination = $posts->pagination();

        $this->assertEquals('http://example.org/page/5/?s=elephant', $pagination->pages[4]['link']);
    }

    #[PermalinkStructure('/%postname%/')]
    public function testCollectionPaginationNextUsesBaseAndFormatArgs()
    {
        $posts = static::factory()->post->create_many(55);
        $this->get(\home_url('/'));
        $posts = Timber::get_posts();
        $pagination = $posts->pagination([
            'base' => '/apricot/%_%',
            'format' => 'page/%#%',
        ]);

        $this->assertEquals('/apricot/page/2/', $pagination->next['link']);
    }

    #[PermalinkStructure('/%postname%/')]
    public function testCollectionPaginationPrevUsesBaseAndFormatArgs()
    {
        // Reset REQUEST_URI - custom base/format pagination shouldn't inherit query params
        $this->get('/');

        for ($i = 1; $i < 30; $i++) {
            static::factory()->post->create([
                'post_title' => 'post' . $i,
                'post_date' => '2014-03-' . \str_pad($i, 2, '0', STR_PAD_LEFT) . ' 00:00:00',
            ]);
        }
        $posts = Timber::get_posts([
            'paged' => 3,
        ]);
        $pagination = $posts->pagination([
            'base' => '/apricot/%_%',
            'format' => '?pagination=%#%',
        ]);

        $this->assertEquals('/apricot/?pagination=2', $pagination->prev['link']);
    }

    #[PermalinkStructure('/%postname%/')]
    public function testCollectionPaginationPrevUsesBaseAndFormatArgsPage()
    {
        // Reset REQUEST_URI - custom base/format pagination shouldn't inherit query params
        $this->get('/');

        static::factory()->post->create_many(30);

        // Query for the third page of posts. Exactly two pages should precede this page.
        $posts = Timber::get_posts([
            'paged' => 3,
        ]);
        $pagination = $posts->pagination([
            'base' => '/apricot/%_%',
            'format' => '?page=%#%',
        ]);

        $this->assertEquals('/apricot/?page=2', $pagination->prev['link']);
    }

    #[PermalinkStructure('/%postname%/')]
    public function testCollectionPaginationWithMoreThan10Pages()
    {
        $posts = static::factory()->post->create_many(150);
        $this->get(\home_url('/page/13'));
        $posts = new PostQuery($GLOBALS['wp_query']);
        $expected_next_link = \user_trailingslashit('http://example.org/page/14/');
        $pagination = $posts->pagination();

        $this->assertEquals($expected_next_link, $pagination->next['link']);
    }

    public function testPostCollectionPaginationForMultiplePostTypes()
    {
        \register_post_type('recipe');

        $pids = static::factory()->post->create_many(43, [
            'post_type' => 'recipe',
        ]);
        $recipes = new PostQuery(new WP_Query('post_type=recipe'));
        $pagination = $recipes->pagination();
        $this->assertSame(5, \count($pagination->pages));
        $pids = static::factory()->post->create_many(13);

        $posts = new PostQuery(new WP_Query('post_type=post'));
        $pagination = $posts->pagination();
        $this->assertSame(2, \count($pagination->pages));

        // clean up
        \unregister_post_type('recipe');
    }

    #[PermalinkStructure('/%postname%/')]
    #[Ticket('#2123')]
    public function testLittlePaginationCategory()
    {
        // setup
        $posts = static::factory()->post->create_many(3, [
            'post_type' => 'post',
        ]);
        $zonk_id = \wp_insert_term('Zonk', 'category');
        foreach ($posts as $post) {
            \wp_set_object_terms($post, $zonk_id, 'category');
        }
        $this->get(\home_url('/category/zonk'));
        // create page query
        $category_slug = 'zonk';
        $paged = 1;
        $context = Timber::context();
        $context['posts'] = Timber::get_posts([
            'posts_per_page' => 3,
            'orderby' => 'date',
            'order' => 'DESC',
            'category_name' => $category_slug,
            'paged' => $paged,
        ]);
        $pagination = $context['posts']->pagination([
            'show_all' => false,
            'mid_size' => 1,
            'end_size' => 2,
        ]);
        $this->assertSame(0, \count($pagination->pages));
    }

    #[PermalinkStructure('/%year%/%postname%/')]
    #[Ticket('#1459')]
    public function test1459Pagintion()
    {
        static::factory()->post->create_many(9, [
            'post_type' => 'my_cpt',
        ]);

        $this->get(\home_url('my_cpt'));
        $data['posts'] = Timber::get_posts([
            'post_type' => 'my_cpt',
            'posts_per_page' => 4,
            'paged' => 1,
        ]);
        \wp_reset_query(); // for good measure
        $pagination = $data['posts']->pagination();
        $this->assertEquals('http://example.org/my_cpt/page/3/', $pagination->pages[2]['link']);
    }

    #[Ticket('#2302')]
    public function testPaginationEndLimits()
    {
        $pids = static::factory()->post->create_many(150);
        // Test defaults (mid = 2, end = 1, start = end)
        $posts = Timber::get_posts([
            'post_type' => 'post',
            'paged' => 13,
            'posts_per_page' => 5,
        ]);
        $pagination = $posts->pagination([
            'show_all' => false,
        ]);
        $this->assertSame(11, \count($pagination->pages));
        // Test mid_size
        $posts = Timber::get_posts([
            'post_type' => 'post',
            'paged' => 13,
            'posts_per_page' => 5,
        ]);
        $pagination = $posts->pagination([
            'show_all' => false,
            'mid_size' => 1,
        ]);
        $this->assertSame(7, \count($pagination->pages));
        // Test mid_size = 0
        $posts = Timber::get_posts([
            'post_type' => 'post',
            'paged' => 13,
            'posts_per_page' => 5,
        ]);
        $pagination = $posts->pagination([
            'show_all' => false,
            'mid_size' => 0,
        ]);
        $this->assertSame(5, \count($pagination->pages));
        // Test end_size
        $posts = Timber::get_posts([
            'post_type' => 'post',
            'paged' => 13,
            'posts_per_page' => 5,
        ]);
        $pagination = $posts->pagination([
            'show_all' => false,
            'end_size' => 2,
        ]);
        $this->assertSame(13, \count($pagination->pages));
        // Test end_size = 0
        $posts = Timber::get_posts([
            'post_type' => 'post',
            'paged' => 13,
            'posts_per_page' => 5,
        ]);
        $pagination = $posts->pagination([
            'show_all' => false,
            'end_size' => 0,
        ]);
        $this->assertSame(9, \count($pagination->pages));
        // Test start_size
        $posts = Timber::get_posts([
            'post_type' => 'post',
            'paged' => 13,
            'posts_per_page' => 5,
        ]);
        $pagination = $posts->pagination([
            'show_all' => false,
            'start_size' => 2,
        ]);
        $this->assertSame(12, \count($pagination->pages));
        // Test start_size = 0
        $posts = Timber::get_posts([
            'post_type' => 'post',
            'paged' => 13,
            'posts_per_page' => 5,
        ]);
        $pagination = $posts->pagination([
            'show_all' => false,
            'start_size' => 0,
        ]);
        $this->assertSame(10, \count($pagination->pages));
        // Test start_size, end_size
        $posts = Timber::get_posts([
            'post_type' => 'post',
            'paged' => 13,
            'posts_per_page' => 5,
        ]);
        $pagination = $posts->pagination([
            'show_all' => false,
            'start_size' => 2,
            'end_size' => 3,
        ]);
        $this->assertSame(14, \count($pagination->pages));
        // Test start_size, end_size  = 0
        $posts = Timber::get_posts([
            'post_type' => 'post',
            'paged' => 13,
            'posts_per_page' => 5,
        ]);
        $pagination = $posts->pagination([
            'show_all' => false,
            'start_size' => 2,
            'end_size' => 0,
        ]);
        $this->assertSame(11, \count($pagination->pages));
    }
}
