<?php

use Timber\PostQuery;

/**
 * @group posts-api
 * @group post-collections
 * @group pagination
 */
class TestTimberPagination extends Timber_UnitTestCase {

	public function setUp() {
		parent::setUp();

		$this->setPermalinkStructure('/%postname%/');
		register_post_type( 'portfolio' );
	}

	public function tearDown() {
		parent::tearDown();

		unregister_post_type('portfolio');
	}

	/**
	 * @expectedDeprecated get_pagination
	 */
	function testPaginationSearch() {
		$this->setPermalinkStructure('');
		$posts = $this->factory->post->create_many( 55 );
		$this->go_to( home_url( '?s=post' ) );
		$pagination = Timber::get_pagination();
		$this->assertEquals( user_trailingslashit(home_url().esc_url('/?paged=5&s=post')), $pagination['pages'][4]['link'] );
	}

	/**
	 * @expectedDeprecated get_pagination
	 */
	function testPaginationWithGetPosts() {
		$pids = $this->factory->post->create_many( 33 );
		$pids = $this->factory->post->create_many( 55, array( 'post_type' => 'portfolio' ) );
		$this->go_to( home_url( '/' ) );
		Timber::get_posts('post_type=portfolio');
		$pagination = Timber::get_pagination();

		global $timber;
		$timber->active_query = false;
		unset($timber->active_query);
		$this->assertEquals(4, count($pagination['pages']));
	}

	function testPaginationWithPostQuery() {
		$pids = $this->factory->post->create_many( 33 );
		$pids = $this->factory->post->create_many( 55, array( 'post_type' => 'portfolio' ) );
		$this->go_to( home_url( '/' ) );

		// @todo once the Posts API uses Factories, simplify this to Timber::get_posts([...])
		$query = new PostQuery([
			'query' => new WP_Query([
				'post_type' => 'portfolio',
			]),
		]);

		$this->assertCount(6, $query->pagination()->pages);
	}

	/**
	 * @expectedDeprecated get_pagination
	 */
	function testPaginationOnLaterPage() {
		$pids = $this->factory->post->create_many( 55, array( 'post_type' => 'portfolio' ) );
		$this->go_to( home_url( '/portfolio/page/3' ) );
		query_posts('post_type=portfolio&paged=3');
		$pagination = Timber::get_pagination();
		$this->assertEquals(6, count($pagination['pages']));
	}

	/**
	 * @expectedDeprecated get_pagination
	 */
	function testSanitizeNextPagination() {
		$pids = $this->factory->post->create_many( 55, array( 'post_type' => 'portfolio' ) );
		$this->go_to( home_url( '/portfolio/page/3?whscheck="><svg/onload=alert()>' ) );
		query_posts('post_type=portfolio&paged=3');
		$pagination = Timber::get_pagination();
		$this->assertEquals('http://example.org/portfolio/page/4/?whscheck=%22%3E%3Csvg%2Fonload%3Dalert%28%29%3E', $pagination['next']['link']);
	}

	/**
	 * @expectedDeprecated get_pagination
	 */
	function testMaliciousGetParameter() {
		$this->factory->post->create_many( 33, array( 'post_type' => 'portfolio' ) );
		$this->go_to( home_url( '/portfolio/page/3?wx9um%2522%253e%253cscript%253ealert%25281%2529%253c%252fscript%
253eaq86s=1' ) );
		query_posts('post_type=portfolio&paged=3');
		$pagination = Timber::get_pagination();
		$this->assertEquals('http://example.org/portfolio/page/4/?wx9umscriptalert(1)/script%_253eaq86s=1', $pagination['next']['link']);
	}

	/**
	 * @expectedDeprecated get_pagination
	 */
	function testMaliciousGetParameter2() {
		$this->factory->post->create_many( 33, array( 'post_type' => 'portfolio' ) );

		$encoded_once = '?%22%3E%3Cscript%3Ealert(%22XSS%20XSS%22)%3C%2Fscript%3E%3D1';
		$this->go_to( home_url( "/portfolio/page/3?{$encoded_once}" ) );
		query_posts('post_type=portfolio&paged=3');
		$pagination = Timber::get_pagination();
		$this->assertEquals("http://example.org/portfolio/page/4/?scriptalert(XSS_XSS)/script=1", $pagination['next']['link']);
		$encoded_twice = '?%2522%253E%253Cscript%253Ealert(%2522XSS%2520XSS%2522)%253C%252Fscript%253E%253D1';
		$this->go_to( home_url( "/portfolio/page/3?{$encoded_twice}" ) );
		query_posts('post_type=portfolio&paged=3');
		$pagination = Timber::get_pagination();
		$this->assertEquals("http://example.org/portfolio/page/4/?scriptalert(XSS_XSS)/script=1", $pagination['next']['link']);
	}

	function testDoubleEncodedPaginationUrl() {
		$this->factory->post->create_many( 33, array( 'post_type' => 'portfolio' ) );
		$this->go_to( home_url( '/portfolio/page/3?wx9um%2522%253e%253cscript%253ealert%25281%2529%253c%252fscript%
253eaq86s=1' ) );
		query_posts('post_type=portfolio&paged=3');

		$link = Timber::compile_string("{{ posts.pagination.next.link|e('esc_url') }}", array(
			'posts' => new Timber\PostQuery(),
		) );
		$this->assertEquals('http://example.org/portfolio/page/4/?wx9umscriptalert(1)/script%_253eaq86s=1', $link);
	}

	function testDoubleEncodedPaginationUrlWithEscHTML() {
		$this->factory->post->create_many( 33, array( 'post_type' => 'portfolio' ) );
		$this->go_to( home_url( '/portfolio/page/3?wx9um%2522%253e%253cscript%253ealert%25281%2529%253c%252fscript%
253eaq86s=1' ) );
		query_posts('post_type=portfolio&paged=3');

		$link = Timber::compile_string("{{ posts.pagination.next.link|e('esc_html') }}", array(
			'posts' => new Timber\PostQuery(),
		) );
		$this->assertEquals('http://example.org/portfolio/page/4/?wx9umscriptalert(1)/script%_253eaq86s=1', $link);
	}

	/**
	 * @expectedDeprecated get_pagination
	 */
	function testPaginationWithSize() {
		$pids = $this->factory->post->create_many( 99, array( 'post_type' => 'portfolio' ) );
		query_posts('post_type=portfolio');
		$pagination = Timber::get_pagination(4);
		$this->assertEquals(5, count($pagination['pages']));
	}

	/**
	 * @expectedDeprecated get_pagination
	 */
	function testPaginationSearchPrettyWithPostname() {
		$posts = $this->factory->post->create_many( 55 );
		$archive = home_url( '?s=post' );
		$this->go_to( $archive );
		query_posts( 's=post' );
		$pagination = Timber::get_pagination();
		$this->assertEquals( 'http://example.org/page/5/?s=post', $pagination['pages'][4]['link'] );
	}

	/**
	 * @expectedDeprecated get_pagination
	 */
	function testPaginationSearchPrettyWithPostnameNext() {
		$posts = $this->factory->post->create_many( 55 );
		$archive = home_url( '?s=post' );
		$this->go_to( $archive );
		query_posts( 's=post' );
		$pagination = Timber::get_pagination();
		$this->assertEquals( 'http://example.org/page/2/?s=post', $pagination['next']['link'] );
	}

	/**
	 * @expectedDeprecated get_pagination
	 */
	function testPaginationSearchPrettyWithPostnamePrev() {
		$posts = $this->factory->post->create_many( 55 );
		$archive = home_url( 'page/4/?s=post' );
		$this->go_to( $archive );
		query_posts( 's=post&paged=4' );
		$pagination = Timber::get_pagination();
		$this->assertEquals( 'http://example.org/page/3/?s=post', $pagination['prev']['link'] );
	}

	/**
	 * @expectedDeprecated get_pagination
	 */
	function testPaginationSearchPrettyx() {
		$this->setPermalinkStructure( '/blog/%year%/%monthnum%/%postname%/' );
		$posts = $this->factory->post->create_many( 55 );
		$archive = home_url( '?s=post' );
		$this->go_to( $archive );
		$pagination = Timber::get_pagination();
		$this->assertEquals( 'http://example.org/page/5/?s=post', $pagination['pages'][4]['link'] );
	}

	/**
	 * @expectedDeprecated get_pagination
	 */
	function testPaginationHomePrettyTrailingSlash() {
		$this->setPermalinkStructure('/%postname%/');
		$posts = $this->factory->post->create_many( 55 );
		$this->go_to( home_url( '/' ) );
		$pagination = Timber::get_pagination();
		$this->assertEquals( user_trailingslashit('http://example.org/page/3/'), $pagination['pages'][2]['link'] );
		$this->assertEquals( user_trailingslashit('http://example.org/page/2/'), $pagination['next']['link'] );
	}

	/**
	 * @expectedDeprecated get_pagination
	 */
	function testPaginationHomePrettyNonTrailingSlash() {
		$this->setPermalinkStructure('/%postname%');
		$posts = $this->factory->post->create_many( 55 );
		$this->go_to( home_url( '/' ) );
		$pagination = Timber::get_pagination();
		$this->assertEquals( 'http://example.org/page/3', $pagination['pages'][2]['link'] );
		$this->assertEquals( 'http://example.org/page/2', $pagination['next']['link'] );
	}

	function testPaginationInCategory() {
		$no_posts = $this->factory->post->create_many( 73 );
		$posts = $this->factory->post->create_many( 31 );
		$news_id = wp_insert_term( 'News', 'category' );
		foreach ( $posts as $post ) {
			wp_set_object_terms( $post, $news_id, 'category' );
		}
		$this->go_to( home_url( '/category/news' ) );
		query_posts('category_name=news');
		$post_objects = new Timber\PostQuery( array(
			'query' => false,
		) );
		$pagination = $post_objects->pagination();
		$this->assertEquals(4, count($pagination->pages));
	}

	/**
	 * @expectedDeprecated get_pagination
	 */
	function testPaginationNextUsesBaseAndFormatArgs() {
		$posts = $this->factory->post->create_many( 55 );
		$this->go_to( home_url( '/' ) );
		$pagination = Timber::get_pagination( array( 'base' => '/apricot/%_%', 'format' => '?pagination=%#%' ) );
		$this->assertEquals( '/apricot/?pagination=2', $pagination['next']['link'] );
	}

	/**
	 * @expectedDeprecated get_pagination
	 */
	function testPaginationPrevUsesBaseAndFormatArgs() {
		$posts = $this->factory->post->create_many( 55 );
		$this->go_to( home_url( '/apricot/page=3' ) );
		query_posts('paged=3');
		$GLOBALS['paged'] = 3;
		$pagination = Timber::get_pagination( array( 'base' => '/apricot/%_%', 'format' => 'pagination/%#%' ) );
		$this->assertEquals( '/apricot/pagination/2/', $pagination['prev']['link'] );
	}

	/**
	 * @expectedDeprecated get_pagination
	 */
	function testPaginationWithMoreThan10Pages() {
		$posts = $this->factory->post->create_many( 150 );
		$this->go_to( home_url( '/page/13' ) );
		$pagination = Timber::get_pagination();
		$expected_next_link = user_trailingslashit('http://example.org/page/14/');
		$this->assertEquals( $expected_next_link, $pagination['next']['link'] );
	}

	// tests for pagination object set on PostCollection

	function testPostsCollectionPagination() {
		$pids = $this->factory->post->create_many( 13 );
		$posts = new Timber\PostQuery( array(
			'query' => array(
				'post_type' => 'post'
			)
		) );
		$pagination = $posts->pagination();
		$this->assertEquals( 2, count( $pagination->pages ) );
	}

	function testCollectionPaginationSearch() {
		$this->setPermalinkStructure('');
		$posts = $this->factory->post->create_many( 55 );
		$this->go_to( home_url( '?s=post' ) );
		$posts = new Timber\PostQuery();
		$pagination = $posts->pagination();
		$this->assertEquals( home_url().esc_url('/?paged=5&s=post'), $pagination->pages[4]['link'] );
	}

	function testCollectionPaginationOnLaterPage() {
		$pids = $this->factory->post->create_many( 55, array( 'post_type' => 'portfolio' ) );
		$this->go_to( home_url( '/portfolio/page/3' ) );
		$posts = new Timber\PostQuery( array(
			'query' => 'post_type=portfolio&paged=3'
		) );
		$pagination = $posts->pagination();
		$this->assertEquals(6, count($pagination->pages));
	}

	function testCollectionPaginationWithSize() {
		$this->setPermalinkStructure('/%postname%/');
		$pids = $this->factory->post->create_many( 99, array( 'post_type' => 'portfolio' ) );
		$posts = new Timber\PostQuery( array(
			'query' => 'post_type=portfolio&posts_per_page=20',
		) );
		$pagination = $posts->pagination();
		$this->assertEquals(5, count($pagination->pages));
	}

	function testCollectionPaginationSearchPrettyWithPostname() {
		$this->setPermalinkStructure('/%postname%/');
		$posts = $this->factory->post->create_many( 55 );
		$archive = home_url('?s=post');
		$this->go_to( $archive );
		$posts = new Timber\PostQuery( array(
			'query' => 's=post'
		) );
		$pagination = $posts->pagination();
		$this->assertEquals( 'http://example.org/page/5/?s=post', $pagination->pages[4]['link'] );
	}

	function testCollectionPaginationSearchPrettyWithPostnameNext() {
		$this->setPermalinkStructure('/%postname%/');
		$posts = $this->factory->post->create_many( 55 );
		$archive = home_url( '?s=post' );
		$this->go_to( $archive );
		$posts = new Timber\PostQuery( array(
			'query' => 's=post'
		) );
		$pagination = $posts->pagination();
		$this->assertEquals( 'http://example.org/page/2/?s=post', $pagination->next['link'] );
	}

	function testCollectionPaginationQueryVars() {
		global $wp;
		$wp->add_query_var( 'myvar' );
		$this->setPermalinkStructure('/%postname%/');
		$posts = $this->factory->post->create_many( 55 );
		$this->go_to( home_url('?myvar=value') );
		$posts = new Timber\PostQuery();
		$pagination = $posts->pagination();
		$this->assertEquals( 'http://example.org/page/2/?myvar=value', $pagination->next['link'] );
	}

	function testCollectionPaginationSearchPrettyWithPostnamePrev() {
		$posts = $this->factory->post->create_many( 55 );
		$archive = home_url( 'page/4/?s=post' );
		$this->go_to( $archive );
		$posts = new Timber\PostQuery( array(
			'query' => 's=post&paged=4'
		) );
		$pagination = $posts->pagination();
		$this->assertEquals( 'http://example.org/page/3/?s=post', $pagination->prev['link'] );
	}

	function testCollectionPaginationSearchPretty() {
		$this->setPermalinkStructure( '/blog/%year%/%monthnum%/%postname%/' );
		$posts = $this->factory->post->create_many( 55 );
		$archive = home_url( '?s=post' );
		$this->go_to( $archive );
		$posts = new Timber\PostQuery();
		$pagination = $posts->pagination();
		$this->assertEquals( 'http://example.org/page/5/?s=post', $pagination->pages[4]['link'] );
	}

	function testCollectionPaginationNextUsesBaseAndFormatArgs() {
		$posts = $this->factory->post->create_many( 55 );
		$this->go_to( home_url( '/' ) );
		$posts = new Timber\PostQuery();
		$pagination = $posts->pagination( array( 'base' => '/apricot/%_%', 'format' => 'page/%#%' ) );
		$this->assertEquals( '/apricot/page/2/', $pagination->next['link'] );
	}

	function testCollectionPaginationPrevUsesBaseAndFormatArgs() {
		for($i=0; $i<30; $i++) {
			$this->factory->post->create(array('post_title' => 'post'.$i, 'post_date' => '2014-02-'.$i));
		}
		$posts = new Timber\PostQuery( array(
			'query' => 'paged=3'
		) );
		$pagination = $posts->pagination( array( 'base' => '/apricot/%_%', 'format' => '?pagination=%#%' ) );
		$this->assertEquals( '/apricot/?pagination=2', $pagination->prev['link'] );
	}

	function testCollectionPaginationPrevUsesBaseAndFormatArgsPage() {
		for($i=0; $i<30; $i++) {
			$this->factory->post->create(array('post_title' => 'post'.$i, 'post_date' => '2014-02-'.$i));
		}
		$posts = new Timber\PostQuery( array(
			'query' => 'paged=3'
		) );
		$pagination = $posts->pagination( array( 'base' => '/apricot/%_%', 'format' => '?page=%#%' ) );
		$this->assertEquals( '/apricot/?page=2', $pagination->prev['link'] );
	}

	function testCollectionPaginationWithMoreThan10Pages() {
		$posts = $this->factory->post->create_many( 150 );
		$this->go_to( home_url( '/page/13' ) );
		$posts = new Timber\PostQuery();
		$expected_next_link = user_trailingslashit('http://example.org/page/14/');
		$pagination = $posts->pagination();
		$this->assertEquals( $expected_next_link, $pagination->next['link'] );
	}

	function testPostCollectionPaginationForMultiplePostTypes() {
		register_post_type( 'recipe' );

		$pids = $this->factory->post->create_many( 43, array( 'post_type' => 'recipe' ) );
		$recipes = new Timber\PostQuery(array(
			'query' => array(
				'post_type' => 'recipe'
			),
		) );
		$pagination = $recipes->pagination();
		$this->assertEquals( 5, count( $pagination->pages ) );
		$pids = $this->factory->post->create_many( 13 );
		$posts = new Timber\PostQuery( array(
			'query' => array(
				'post_type' => 'post'
			),
		) );
		$pagination = $posts->pagination();
		$this->assertEquals( 2, count( $pagination->pages ) );

		// clean up
		unregister_post_type( 'recipe' );
	}

	/**
	 * @ticket #2123
	 * @expectedDeprecated Passing query arguments directly to PostQuery
	 */
	function testLittlePaginationCateogry() {
		$this->setPermalinkStructure('/%postname%/');
		// setup
		$posts = $this->factory->post->create_many( 3, array( 'post_type' => 'post' ) );
		$zonk_id = wp_insert_term( 'Zonk', 'category' );
		foreach ( $posts as $post ) {
			wp_set_object_terms( $post, $zonk_id, 'category' );
		}
		$this->go_to( home_url( '/category/zonk' ) );
		// create page query
		$category_slug = 'zonk';
		$paged = 1;
		$context = Timber::context();
		$context['posts'] = new Timber\PostQuery([
		    'posts_per_page' => 3,
		    'orderby' => 'date',
		    'order' => 'DESC',
		    'category_name' => $category_slug,
		    'paged' => $paged,
		]);
		$pagination = $context['posts']->pagination(array('show_all' => false, 'mid_size' => 1, 'end_size' => 2));
		$this->assertEquals(0, count($pagination->pages));
	}

	/**
	 * @ticket #1459
	 * @expectedDeprecated Passing query arguments directly to PostQuery
	 */
	function test1459Pagintion() {
		$this->setPermalinkStructure('/%year%/%postname%/');
		global $paged;
		register_post_type('my_cpt', array('public' => true, 'has_archive' => true));
		$posts = $this->factory->post->create_many( 9, array( 'post_type' => 'my_cpt' ) );
		if (!isset($paged) || !$paged){
			$paged = 1;
		}
		$this->go_to( home_url( 'my_cpt' ) );
		$data['posts'] =  new \Timber\PostQuery(['post_type' => 'my_cpt', 'posts_per_page' => 4, 'paged' => $paged]);
	    wp_reset_query(); // for good measure
	    $pagination = $data['posts']->pagination();
	    $this->assertEquals('http://example.org/my_cpt/page/3/', $pagination->pages[2]['link']);
	}



}
