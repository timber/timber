<?php

namespace Timber\Tests;

use Mantle\Testing\Attributes\PermalinkStructure;
use Mantle\Testing\Concerns\Refresh_Database;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\Attributes\Ticket;
use Timber\PostQuery;
use Timber\Tests\Support\Attributes\WithOption;
use Timber\Timber;
use WP_Query;

#[PermalinkStructure('')]
#[Group('posts-api')]
#[Group('post-collections')]
#[Group('pagination')]
#[WithOption('posts_per_page', 2)]
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

    #[IgnoreDeprecations]
    public function testPaginationSearch()
    {
        $this->setExpectedDeprecated('get_pagination');
        $posts = static::factory()->post->create_many(10, [
            'post_title' => 'searchable post',
        ]);
        $this->get(\home_url('/?s=post'));
        $pagination = Timber::get_pagination();

        $this->assertEquals(
            \user_trailingslashit(\esc_url(\home_url('/?paged=5&s=post'))),
            $pagination['pages'][4]['link']
        );
    }

    #[IgnoreDeprecations]
    public function testPaginationWithGetPosts()
    {
        $this->setExpectedDeprecated('get_pagination');
        $pids = static::factory()->post->create_many(7);
        $pids = static::factory()->post->create_many(8, [
            'post_type' => 'portfolio',
        ]);
        $this->get(\home_url('/'));
        Timber::get_posts([
            'post_type' => 'portfolio',
        ]);
        $pagination = Timber::get_pagination();

        $this->assertCount(4, $pagination['pages']);
    }

    public function testPaginationWithPostQuery()
    {
        $pids = static::factory()->post->create_many(3);
        $pids = static::factory()->post->create_many(11, [
            'post_type' => 'portfolio',
        ]);
        $this->get(\home_url('/'));

        $query = Timber::get_posts([
            'post_type' => 'portfolio',
        ]);

        $this->assertCount(6, $query->pagination()->pages);
    }

    #[IgnoreDeprecations]
    public function testPaginationOnLaterPage()
    {
        $this->setExpectedDeprecated('get_pagination');
        $pids = static::factory()->post->create_many(11, [
            'post_type' => 'portfolio',
        ]);
        $this->get(\home_url('?post_type=portfolio&paged=3'));
        $pagination = Timber::get_pagination();
        $this->assertSame(6, \count($pagination['pages']));
    }

    #[PermalinkStructure('/%postname%/')]
    #[IgnoreDeprecations]
    public function testSanitizeNextPagination()
    {
        $this->setExpectedDeprecated('get_pagination');
        $pids = static::factory()->post->create_many(8, [
            'post_type' => 'portfolio',
        ]);
        $this->get(\home_url('/portfolio/page/3?whscheck="><svg/onload=alert()>'));
        $pagination = Timber::get_pagination();
        $this->assertEquals('http://example.org/portfolio/page/4/?whscheck=%22%3E%3Csvg%2Fonload%3Dalert%28%29%3E', $pagination['next']['link']);
    }

    #[PermalinkStructure('/%postname%/')]
    #[IgnoreDeprecations]
    public function testMaliciousGetParameter()
    {
        $this->setExpectedDeprecated('get_pagination');
        static::factory()->post->create_many(8, [
            'post_type' => 'portfolio',
        ]);
        $this->get(\home_url('/portfolio/page/3?wx9um%2522%253e%253cscript%253ealert%25281%2529%253c%252fscript%
253eaq86s=1'));
        $pagination = Timber::get_pagination();
        $this->assertEquals('http://example.org/portfolio/page/4/?wx9umscriptalert(1)/script%_253eaq86s=1', $pagination['next']['link']);
    }

    #[PermalinkStructure('/%postname%/')]
    #[IgnoreDeprecations]
    public function testMaliciousGetParameter2()
    {
        $this->setExpectedDeprecated('get_pagination');
        static::factory()->post->create_many(8, [
            'post_type' => 'portfolio',
        ]);

        $encoded_once = '?%22%3E%3Cscript%3Ealert(%22XSS%20XSS%22)%3C%2Fscript%3E%3D1';
        $this->get(\home_url("/portfolio/page/3?{$encoded_once}"));
        $pagination = Timber::get_pagination();
        $this->assertEquals("http://example.org/portfolio/page/4/?scriptalert(XSS_XSS)/script=1", $pagination['next']['link']);
        $encoded_twice = '?%2522%253E%253Cscript%253Ealert(%2522XSS%2520XSS%2522)%253C%252Fscript%253E%253D1';
        $this->get(\home_url("/portfolio/page/3?{$encoded_twice}"));
        $pagination = Timber::get_pagination();
        $this->assertEquals("http://example.org/portfolio/page/4/?scriptalert(XSS_XSS)/script=1", $pagination['next']['link']);
    }

    #[PermalinkStructure('/%postname%/')]
    public function testDoubleEncodedPaginationUrl()
    {
        static::factory()->post->create_many(8, [
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
        static::factory()->post->create_many(8, [
            'post_type' => 'portfolio',
        ]);
        $this->get(\home_url('/portfolio/page/3?wx9um%2522%253e%253cscript%253ealert%25281%2529%253c%252fscript%
253eaq86s=1'));

        $link = Timber::compile_string("{{ posts.pagination.next.link|e('esc_html') }}", [
            'posts' => new PostQuery($GLOBALS['wp_query']),
        ]);
        $this->assertEquals('http://example.org/portfolio/page/4/?wx9umscriptalert(1)/script%_253eaq86s=1', $link);
    }

    #[IgnoreDeprecations]
    public function testPaginationWithSize()
    {
        $this->setExpectedDeprecated('get_pagination');
        $pids = static::factory()->post->create_many(99, [
            'post_type' => 'portfolio',
        ]);
        $this->get(\home_url('?post_type=portfolio'));
        $pagination = Timber::get_pagination(4);
        $this->assertSame(5, \count($pagination['pages']));
    }

    #[PermalinkStructure('/%postname%/')]
    #[IgnoreDeprecations]
    public function testPaginationSearchPrettyWithPostname()
    {
        $this->setExpectedDeprecated('get_pagination');
        static::factory()->post->create_many(10, [
            'post_title' => 'searchable post',
        ]);
        $archive = \home_url('?s=post');
        $this->get($archive);
        $pagination = Timber::get_pagination();

        $this->assertEquals('http://example.org/page/5/?s=post', $pagination['pages'][4]['link']);
    }

    #[PermalinkStructure('/%postname%/')]
    #[IgnoreDeprecations]
    public function testPaginationSearchPrettyWithPostnameNext()
    {
        $this->setExpectedDeprecated('get_pagination');
        static::factory()->post->create_many(4, [
            'post_title' => 'searchable post',
        ]);
        $archive = \home_url('?s=post');
        $this->get($archive);
        $pagination = Timber::get_pagination();

        $this->assertEquals('http://example.org/page/2/?s=post', $pagination['next']['link']);
    }

    #[PermalinkStructure('/%postname%/')]
    #[IgnoreDeprecations]
    public function testPaginationSearchPrettyWithPostnamePrev()
    {
        $this->setExpectedDeprecated('get_pagination');
        static::factory()->post->create_many(8, [
            'post_title' => 'searchable post',
        ]);

        $archive = \home_url('page/4/?s=post');
        $this->get($archive);
        $pagination = Timber::get_pagination();

        $this->assertEquals('http://example.org/page/3/?s=post', $pagination['prev']['link']);
    }

    #[PermalinkStructure('/blog/%year%/%monthnum%/%postname%/')]
    #[IgnoreDeprecations]
    public function testPaginationSearchPrettyx()
    {
        $this->setExpectedDeprecated('get_pagination');
        static::factory()->post->create_many(10, [
            'post_title' => 'searchable post',
        ]);

        $archive = \home_url('?s=post');
        $this->get($archive);
        $pagination = Timber::get_pagination();

        $this->assertEquals('http://example.org/page/5/?s=post', $pagination['pages'][4]['link']);
    }

    #[PermalinkStructure('/%postname%/')]
    #[IgnoreDeprecations]
    public function testPaginationHomePrettyTrailingSlash()
    {
        $this->setExpectedDeprecated('get_pagination');
        static::factory()->post->create_many(6, [
            'post_title' => 'searchable post',
        ]);

        $this->get(\home_url('/'));
        $pagination = Timber::get_pagination();

        $this->assertEquals(\user_trailingslashit('http://example.org/page/3/'), $pagination['pages'][2]['link']);
        $this->assertEquals(\user_trailingslashit('http://example.org/page/2/'), $pagination['next']['link']);
    }

    #[PermalinkStructure('/%postname%')]
    #[IgnoreDeprecations]
    public function testPaginationHomePrettyNonTrailingSlash()
    {
        $this->setExpectedDeprecated('get_pagination');
        static::factory()->post->create_many(6);
        $this->get(\home_url('/'));
        $pagination = Timber::get_pagination();

        $this->assertEquals('http://example.org/page/3', $pagination['pages'][2]['link']);
        $this->assertEquals('http://example.org/page/2', $pagination['next']['link']);
    }

    public function testPaginationInCategory()
    {
        static::factory()->post->create_many(3);

        $news_id = static::factory()->term->create([
            'name' => 'News',
            'taxonomy' => 'category',
        ]);
        $posts = static::factory()->post->create_many(7);
        foreach ($posts as $post) {
            \wp_set_object_terms($post, $news_id, 'category');
        }

        $this->get(\home_url('?cat=' . $news_id));

        // Let Timber fall back on the main query.
        $pagination = Timber::get_posts()->pagination();

        $this->assertCount(4, $pagination->pages);
    }

    #[PermalinkStructure('/%postname%/')]
    #[IgnoreDeprecations]
    public function testPaginationNextUsesBaseAndFormatArgs()
    {
        $this->setExpectedDeprecated('get_pagination');
        static::factory()->post->create_many(4);
        $this->get(\home_url('/'));
        $pagination = Timber::get_pagination([
            'base' => '/apricot/%_%',
            'format' => '?pagination=%#%',
        ]);

        $this->assertEquals('/apricot/?pagination=2', $pagination['next']['link']);
    }

    #[PermalinkStructure('/%postname%/')]
    #[IgnoreDeprecations]
    public function testPaginationPrevUsesBaseAndFormatArgs()
    {
        $this->setExpectedDeprecated('get_pagination');
        static::factory()->post->create_many(6);
        $this->get(\home_url('/apricot/page=3'));
        \query_posts('paged=3');
        $GLOBALS['paged'] = 3;
        $pagination = Timber::get_pagination([
            'base' => '/apricot/%_%',
            'format' => 'pagination/%#%',
        ]);

        $this->assertEquals('/apricot/pagination/2/', $pagination['prev']['link']);
    }

    #[PermalinkStructure('/%postname%/')]
    #[IgnoreDeprecations]
    public function testPaginationWithMoreThan10Pages()
    {
        $this->setExpectedDeprecated('get_pagination');
        static::factory()->post->create_many(28);
        $this->get(\home_url('/page/13'));
        $pagination = Timber::get_pagination();
        $expected_next_link = \user_trailingslashit('http://example.org/page/14/');

        $this->assertEquals($expected_next_link, $pagination['next']['link']);
    }

    // tests for pagination object set on PostCollection

    public function testPostsCollectionPagination()
    {
        static::factory()->post->create_many(3);
        $pagination = Timber::get_posts([
            'post_type' => 'post',
        ])->pagination();

        $this->assertCount(2, $pagination->pages);
    }

    #[PermalinkStructure('')]
    public function testCollectionPaginationSearch()
    {
        static::factory()->post->create_many(10, [
            'post_title' => 'searchable post',
        ]);
        $this->get(\home_url('?s=post'));
        $posts = new PostQuery($GLOBALS['wp_query']);
        $pagination = $posts->pagination();

        $this->assertEquals(\home_url() . \esc_url('/?paged=5&s=post'), $pagination->pages[4]['link']);
    }

    public function testCollectionPaginationOnLaterPage()
    {
        static::factory()->post->create_many(11, [
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
        static::factory()->post->create_many(10, [
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
        static::factory()->post->create_many(4, [
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
        static::factory()->post->create_many(4);
        $this->get(\home_url('?myvar=value'));
        $posts = new PostQuery($GLOBALS['wp_query']);
        $pagination = $posts->pagination();

        $this->assertEquals('http://example.org/page/2/?myvar=value', $pagination->next['link']);
    }

    #[PermalinkStructure('/%postname%/')]
    public function testCollectionPaginationSearchPrettyWithPostnamePrev()
    {
        static::factory()->post->create_many(8, [
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
        static::factory()->post->create_many(10, [
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
        $posts = static::factory()->post->create_many(4);
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

        static::factory()->post->create_many(6);

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
        $posts = static::factory()->post->create_many(28);
        $this->get(\home_url('/page/13'));
        $posts = new PostQuery($GLOBALS['wp_query']);
        $expected_next_link = \user_trailingslashit('http://example.org/page/14/');
        $pagination = $posts->pagination();

        $this->assertEquals($expected_next_link, $pagination->next['link']);
    }

    public function testPostCollectionPaginationForMultiplePostTypes()
    {
        \register_post_type('recipe');

        $pids = static::factory()->post->create_many(9, [
            'post_type' => 'recipe',
        ]);
        $recipes = new PostQuery(new WP_Query('post_type=recipe'));
        $pagination = $recipes->pagination();
        $this->assertSame(5, \count($pagination->pages));
        $pids = static::factory()->post->create_many(3);

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
        // Total pages (30) is what drives the start/mid/end_size math here, not the
        // per-page value itself. Keep 30 pages but with pp=2 to cut fixture cost.
        $pids = static::factory()->post->create_many(60);
        // Test defaults (mid = 2, end = 1, start = end)
        $posts = Timber::get_posts([
            'post_type' => 'post',
            'paged' => 13,
            'posts_per_page' => 2,
        ]);
        $pagination = $posts->pagination([
            'show_all' => false,
        ]);
        $this->assertSame(11, \count($pagination->pages));
        // Test mid_size
        $posts = Timber::get_posts([
            'post_type' => 'post',
            'paged' => 13,
            'posts_per_page' => 2,
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
            'posts_per_page' => 2,
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
            'posts_per_page' => 2,
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
            'posts_per_page' => 2,
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
            'posts_per_page' => 2,
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
            'posts_per_page' => 2,
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
            'posts_per_page' => 2,
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
            'posts_per_page' => 2,
        ]);
        $pagination = $posts->pagination([
            'show_all' => false,
            'start_size' => 2,
            'end_size' => 0,
        ]);
        $this->assertSame(11, \count($pagination->pages));
    }
}
