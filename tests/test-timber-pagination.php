<?php

use Timber\PostQuery;

/**
 * @group posts-api
 * @group post-collections
 * @group pagination
 */
class TestTimberPagination extends Timber_UnitTestCase
{
    public function set_up()
    {
        parent::set_up();

        $this->setPermalinkStructure('/%postname%/');
        register_post_type('portfolio');
    }

    public function tear_down()
    {
        parent::tear_down();

        unregister_post_type('portfolio');
    }

    /**
     * @expectedDeprecated get_pagination
     */
    public function testPaginationSearch()
    {
        $this->setPermalinkStructure('');
        $posts = $this->factory->post->create_many(55);
        $this->go_to(home_url('?s=post'));
        $pagination = Timber::get_pagination();
        $this->assertEquals(user_trailingslashit(home_url() . esc_url('/?paged=5&s=post')), $pagination['pages'][4]['link']);
    }

    /**
     * @expectedDeprecated get_pagination
     */
    public function testPaginationWithGetPosts()
    {
        $pids = $this->factory->post->create_many(33);
        $pids = $this->factory->post->create_many(55, [
            'post_type' => 'portfolio',
        ]);
        $this->go_to(home_url('/'));
        Timber::get_posts([
            'post_type' => 'portfolio',
        ]);
        $pagination = Timber::get_pagination();

        $this->assertCount(4, $pagination['pages']);
    }

    public function testPaginationWithPostQuery()
    {
        $pids = $this->factory->post->create_many(33);
        $pids = $this->factory->post->create_many(55, [
            'post_type' => 'portfolio',
        ]);
        $this->go_to(home_url('/'));

        $query = Timber::get_posts([
            'post_type' => 'portfolio',
        ]);

        $this->assertCount(6, $query->pagination()->pages);
    }

    /**
     * @expectedDeprecated get_pagination
     */
    public function testPaginationOnLaterPage()
    {
        $pids = $this->factory->post->create_many(55, [
            'post_type' => 'portfolio',
        ]);
        $this->go_to(home_url('/portfolio/page/3'));
        query_posts('post_type=portfolio&paged=3');
        $pagination = Timber::get_pagination();
        $this->assertSame(6, count($pagination['pages']));
    }

    /**
     * @expectedDeprecated get_pagination
     */
    public function testSanitizeNextPagination()
    {
        $pids = $this->factory->post->create_many(55, [
            'post_type' => 'portfolio',
        ]);
        $this->go_to(home_url('/portfolio/page/3?whscheck="><svg/onload=alert()>'));
        query_posts('post_type=portfolio&paged=3');
        $pagination = Timber::get_pagination();
        $this->assertEquals('http://example.org/portfolio/page/4/?whscheck=%22%3E%3Csvg%2Fonload%3Dalert%28%29%3E', $pagination['next']['link']);
    }

    /**
     * @expectedDeprecated get_pagination
     */
    public function testMaliciousGetParameter()
    {
        $this->factory->post->create_many(33, [
            'post_type' => 'portfolio',
        ]);
        $this->go_to(home_url('/portfolio/page/3?wx9um%2522%253e%253cscript%253ealert%25281%2529%253c%252fscript%
253eaq86s=1'));
        query_posts('post_type=portfolio&paged=3');
        $pagination = Timber::get_pagination();
        $this->assertEquals('http://example.org/portfolio/page/4/?wx9umscriptalert(1)/script%_253eaq86s=1', $pagination['next']['link']);
    }

    /**
     * @expectedDeprecated get_pagination
     */
    public function testMaliciousGetParameter2()
    {
        $this->factory->post->create_many(33, [
            'post_type' => 'portfolio',
        ]);

        $encoded_once = '?%22%3E%3Cscript%3Ealert(%22XSS%20XSS%22)%3C%2Fscript%3E%3D1';
        $this->go_to(home_url("/portfolio/page/3?{$encoded_once}"));
        query_posts('post_type=portfolio&paged=3');
        $pagination = Timber::get_pagination();
        $this->assertEquals("http://example.org/portfolio/page/4/?scriptalert(XSS_XSS)/script=1", $pagination['next']['link']);
        $encoded_twice = '?%2522%253E%253Cscript%253Ealert(%2522XSS%2520XSS%2522)%253C%252Fscript%253E%253D1';
        $this->go_to(home_url("/portfolio/page/3?{$encoded_twice}"));
        query_posts('post_type=portfolio&paged=3');
        $pagination = Timber::get_pagination();
        $this->assertEquals("http://example.org/portfolio/page/4/?scriptalert(XSS_XSS)/script=1", $pagination['next']['link']);
    }

    public function testDoubleEncodedPaginationUrl()
    {
        $this->factory->post->create_many(33, [
            'post_type' => 'portfolio',
        ]);
        $this->go_to(home_url('/portfolio/page/3?wx9um%2522%253e%253cscript%253ealert%25281%2529%253c%252fscript%
253eaq86s=1'));
        query_posts('post_type=portfolio&paged=3');

        $link = Timber::compile_string("{{ posts.pagination.next.link|e('esc_url') }}", [
            'posts' => new PostQuery($GLOBALS['wp_query']),
        ]);
        $this->assertEquals('http://example.org/portfolio/page/4/?wx9umscriptalert(1)/script%_253eaq86s=1', $link);
    }

    public function testDoubleEncodedPaginationUrlWithEscHTML()
    {
        $this->factory->post->create_many(33, [
            'post_type' => 'portfolio',
        ]);
        $this->go_to(home_url('/portfolio/page/3?wx9um%2522%253e%253cscript%253ealert%25281%2529%253c%252fscript%
253eaq86s=1'));
        query_posts('post_type=portfolio&paged=3');

        $link = Timber::compile_string("{{ posts.pagination.next.link|e('esc_html') }}", [
            'posts' => new PostQuery($GLOBALS['wp_query']),
        ]);
        $this->assertEquals('http://example.org/portfolio/page/4/?wx9umscriptalert(1)/script%_253eaq86s=1', $link);
    }

    /**
     * @expectedDeprecated get_pagination
     */
    public function testPaginationWithSize()
    {
        $pids = $this->factory->post->create_many(99, [
            'post_type' => 'portfolio',
        ]);
        query_posts('post_type=portfolio');
        $pagination = Timber::get_pagination(4);
        $this->assertSame(5, count($pagination['pages']));
    }

    /**
     * @expectedDeprecated get_pagination
     */
    public function testPaginationSearchPrettyWithPostname()
    {
        $posts = $this->factory->post->create_many(55);
        $archive = home_url('?s=post');
        $this->go_to($archive);
        query_posts('s=post');
        $pagination = Timber::get_pagination();
        $this->assertEquals('http://example.org/page/5/?s=post', $pagination['pages'][4]['link']);
    }

    /**
     * @expectedDeprecated get_pagination
     */
    public function testPaginationSearchPrettyWithPostnameNext()
    {
        $posts = $this->factory->post->create_many(55);
        $archive = home_url('?s=post');
        $this->go_to($archive);
        query_posts('s=post');
        $pagination = Timber::get_pagination();
        $this->assertEquals('http://example.org/page/2/?s=post', $pagination['next']['link']);
    }

    /**
     * @expectedDeprecated get_pagination
     */
    public function testPaginationSearchPrettyWithPostnamePrev()
    {
        $posts = $this->factory->post->create_many(55);
        $archive = home_url('page/4/?s=post');
        $this->go_to($archive);
        query_posts('s=post&paged=4');
        $pagination = Timber::get_pagination();
        $this->assertEquals('http://example.org/page/3/?s=post', $pagination['prev']['link']);
    }

    /**
     * @expectedDeprecated get_pagination
     */
    public function testPaginationSearchPrettyx()
    {
        $this->setPermalinkStructure('/blog/%year%/%monthnum%/%postname%/');
        $posts = $this->factory->post->create_many(55);
        $archive = home_url('?s=post');
        $this->go_to($archive);
        $pagination = Timber::get_pagination();
        $this->assertEquals('http://example.org/page/5/?s=post', $pagination['pages'][4]['link']);
    }

    /**
     * @expectedDeprecated get_pagination
     */
    public function testPaginationHomePrettyTrailingSlash()
    {
        $this->setPermalinkStructure('/%postname%/');
        $posts = $this->factory->post->create_many(55);
        $this->go_to(home_url('/'));
        $pagination = Timber::get_pagination();
        $this->assertEquals(user_trailingslashit('http://example.org/page/3/'), $pagination['pages'][2]['link']);
        $this->assertEquals(user_trailingslashit('http://example.org/page/2/'), $pagination['next']['link']);
    }

    /**
     * @expectedDeprecated get_pagination
     */
    public function testPaginationHomePrettyNonTrailingSlash()
    {
        $this->setPermalinkStructure('/%postname%');
        $posts = $this->factory->post->create_many(55);
        $this->go_to(home_url('/'));
        $pagination = Timber::get_pagination();
        $this->assertEquals('http://example.org/page/3', $pagination['pages'][2]['link']);
        $this->assertEquals('http://example.org/page/2', $pagination['next']['link']);
    }

    public function testPaginationInCategory()
    {
        $this->factory->post->create_many(73);

        $news_id = $this->factory->term->create([
            'name' => 'News',
            'taxonomy' => 'category',
        ]);
        $posts = $this->factory->post->create_many(31);
        foreach ($posts as $post) {
            wp_set_object_terms($post, $news_id, 'category');
        }

        // Overwrite the main query.
        query_posts('category_name=news');

        // Let Timber fall back on the main query.
        $pagination = Timber::get_posts()->pagination();

        $this->assertCount(4, $pagination->pages);
    }

    /**
     * @expectedDeprecated get_pagination
     */
    public function testPaginationNextUsesBaseAndFormatArgs()
    {
        $posts = $this->factory->post->create_many(55);
        $this->go_to(home_url('/'));
        $pagination = Timber::get_pagination([
            'base' => '/apricot/%_%',
            'format' => '?pagination=%#%',
        ]);
        $this->assertEquals('/apricot/?pagination=2', $pagination['next']['link']);
    }

    /**
     * @expectedDeprecated get_pagination
     */
    public function testPaginationPrevUsesBaseAndFormatArgs()
    {
        $posts = $this->factory->post->create_many(55);
        $this->go_to(home_url('/apricot/page=3'));
        query_posts('paged=3');
        $GLOBALS['paged'] = 3;
        $pagination = Timber::get_pagination([
            'base' => '/apricot/%_%',
            'format' => 'pagination/%#%',
        ]);
        $this->assertEquals('/apricot/pagination/2/', $pagination['prev']['link']);
    }

    /**
     * @expectedDeprecated get_pagination
     */
    public function testPaginationWithMoreThan10Pages()
    {
        $posts = $this->factory->post->create_many(150);
        $this->go_to(home_url('/page/13'));
        $pagination = Timber::get_pagination();
        $expected_next_link = user_trailingslashit('http://example.org/page/14/');
        $this->assertEquals($expected_next_link, $pagination['next']['link']);
    }

    // tests for pagination object set on PostCollection

    public function testPostsCollectionPagination()
    {
        $this->factory->post->create_many(13);
        $pagination = Timber::get_posts([
            'post_type' => 'post',
        ])->pagination();
        $this->assertCount(2, $pagination->pages);
    }

    public function testCollectionPaginationSearch()
    {
        $this->setPermalinkStructure('');
        $posts = $this->factory->post->create_many(55);
        $this->go_to(home_url('?s=post'));
        $posts = new PostQuery($GLOBALS['wp_query']);
        $pagination = $posts->pagination();
        $this->assertEquals(home_url() . esc_url('/?paged=5&s=post'), $pagination->pages[4]['link']);
    }

    public function testCollectionPaginationOnLaterPage()
    {
        $pids = $this->factory->post->create_many(55, [
            'post_type' => 'portfolio',
        ]);
        $this->go_to(home_url('/portfolio/page/3'));
        $posts = new PostQuery(new WP_Query('post_type=portfolio&paged=3'));
        $pagination = $posts->pagination();
        $this->assertSame(6, count($pagination->pages));
    }

    public function testCollectionPaginationWithSize()
    {
        $this->setPermalinkStructure('/%postname%/');
        $pids = $this->factory->post->create_many(99, [
            'post_type' => 'portfolio',
        ]);
        $posts = new PostQuery(new WP_Query('post_type=portfolio&posts_per_page=20'));
        $pagination = $posts->pagination();
        $this->assertSame(5, count($pagination->pages));
    }

    public function testCollectionPaginationSearchPrettyWithPostname()
    {
        $this->setPermalinkStructure('/%postname%/');
        $posts = $this->factory->post->create_many(55);
        $archive = home_url('?s=post');
        $this->go_to($archive);
        $posts = new PostQuery(new WP_Query('s=post'));
        $pagination = $posts->pagination();
        $this->assertEquals('http://example.org/page/5/?s=post', $pagination->pages[4]['link']);
    }

    public function testCollectionPaginationSearchPrettyWithPostnameNext()
    {
        $this->setPermalinkStructure('/%postname%/');
        $posts = $this->factory->post->create_many(55);
        $archive = home_url('?s=post');
        $this->go_to($archive);
        $posts = new PostQuery(new WP_Query('s=post'));
        $pagination = $posts->pagination();
        $this->assertEquals('http://example.org/page/2/?s=post', $pagination->next['link']);
    }

    public function testCollectionPaginationQueryVars()
    {
        global $wp;
        $wp->add_query_var('myvar');
        $this->setPermalinkStructure('/%postname%/');
        $posts = $this->factory->post->create_many(55);
        $this->go_to(home_url('?myvar=value'));
        $posts = new PostQuery($GLOBALS['wp_query']);
        $pagination = $posts->pagination();
        $this->assertEquals('http://example.org/page/2/?myvar=value', $pagination->next['link']);
    }

    public function testCollectionPaginationSearchPrettyWithPostnamePrev()
    {
        $posts = $this->factory->post->create_many(55);
        $archive = home_url('page/4/?s=post');
        $this->go_to($archive);
        $posts = new PostQuery(new WP_Query('s=post&paged=4'));
        $pagination = $posts->pagination();
        $this->assertEquals('http://example.org/page/3/?s=post', $pagination->prev['link']);
    }

    public function testCollectionPaginationSearchPretty()
    {
        $this->setPermalinkStructure('/blog/%year%/%monthnum%/%postname%/');
        $posts = $this->factory->post->create_many(55);
        $archive = home_url('?s=post');
        $this->go_to($archive);
        $posts = Timber::get_posts();
        $pagination = $posts->pagination();
        $this->assertEquals('http://example.org/page/5/?s=post', $pagination->pages[4]['link']);
    }

    public function testCollectionPaginationNextUsesBaseAndFormatArgs()
    {
        $posts = $this->factory->post->create_many(55);
        $this->go_to(home_url('/'));
        $posts = Timber::get_posts();
        $pagination = $posts->pagination([
            'base' => '/apricot/%_%',
            'format' => 'page/%#%',
        ]);
        $this->assertEquals('/apricot/page/2/', $pagination->next['link']);
    }

    public function testCollectionPaginationPrevUsesBaseAndFormatArgs()
    {
        for ($i = 0; $i < 30; $i++) {
            $this->factory->post->create([
                'post_title' => 'post' . $i,
                'post_date' => '2014-02-' . $i,
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

    public function testCollectionPaginationPrevUsesBaseAndFormatArgsPage()
    {
        $this->factory->post->create_many(30);

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

    public function testCollectionPaginationWithMoreThan10Pages()
    {
        $posts = $this->factory->post->create_many(150);
        $this->go_to(home_url('/page/13'));
        $posts = new PostQuery($GLOBALS['wp_query']);
        $expected_next_link = user_trailingslashit('http://example.org/page/14/');
        $pagination = $posts->pagination();
        $this->assertEquals($expected_next_link, $pagination->next['link']);
    }

    public function testPostCollectionPaginationForMultiplePostTypes()
    {
        register_post_type('recipe');

        $pids = $this->factory->post->create_many(43, [
            'post_type' => 'recipe',
        ]);
        $recipes = new PostQuery(new WP_Query('post_type=recipe'));
        $pagination = $recipes->pagination();
        $this->assertSame(5, count($pagination->pages));
        $pids = $this->factory->post->create_many(13);

        $posts = new PostQuery(new WP_Query('post_type=post'));
        $pagination = $posts->pagination();
        $this->assertSame(2, count($pagination->pages));

        // clean up
        unregister_post_type('recipe');
    }

    /**
     * @ticket #2123
     */
    public function testLittlePaginationCategory()
    {
        $this->setPermalinkStructure('/%postname%/');
        // setup
        $posts = $this->factory->post->create_many(3, [
            'post_type' => 'post',
        ]);
        $zonk_id = wp_insert_term('Zonk', 'category');
        foreach ($posts as $post) {
            wp_set_object_terms($post, $zonk_id, 'category');
        }
        $this->go_to(home_url('/category/zonk'));
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
        $this->assertSame(0, count($pagination->pages));
    }

    /**
     * @ticket #1459
     */
    public function test1459Pagintion()
    {
        $this->setPermalinkStructure('/%year%/%postname%/');
        global $paged;
        register_post_type('my_cpt', [
            'public' => true,
            'has_archive' => true,
        ]);
        $posts = $this->factory->post->create_many(9, [
            'post_type' => 'my_cpt',
        ]);
        if (!isset($paged) || !$paged) {
            $paged = 1;
        }
        $this->go_to(home_url('my_cpt'));
        $data['posts'] = Timber::get_posts([
            'post_type' => 'my_cpt',
            'posts_per_page' => 4,
            'paged' => $paged,
        ]);
        wp_reset_query(); // for good measure
        $pagination = $data['posts']->pagination();
        $this->assertEquals('http://example.org/my_cpt/page/3/', $pagination->pages[2]['link']);
    }

    /**
     * @ticket #2302
     */
    public function testPaginationEndLimits()
    {
        $pids = $this->factory->post->create_many(150);
        // Test defaults (mid = 2, end = 1, start = end)
        $posts = Timber::get_posts([
            'post_type' => 'post',
            'paged' => 13,
            'posts_per_page' => 5,
        ]);
        $pagination = $posts->pagination([
            'show_all' => false,
        ]);
        $this->assertSame(11, count($pagination->pages));
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
        $this->assertSame(7, count($pagination->pages));
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
        $this->assertSame(5, count($pagination->pages));
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
        $this->assertSame(13, count($pagination->pages));
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
        $this->assertSame(9, count($pagination->pages));
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
        $this->assertSame(12, count($pagination->pages));
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
        $this->assertSame(10, count($pagination->pages));
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
        $this->assertSame(14, count($pagination->pages));
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
        $this->assertSame(11, count($pagination->pages));
    }
}
