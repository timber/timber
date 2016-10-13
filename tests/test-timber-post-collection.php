<?php

class TestTimberPostQuery extends Timber_UnitTestCase {

	function setUp() {
		global $wpdb;
		$wpdb->query("TRUNCATE TABLE $wpdb->posts"); 
		$wpdb->query("ALTER TABLE $wpdb->posts AUTO_INCREMENT = 1");
		parent::setUp();
	}

	function testBackwards() {
		$pc = new TimberPostsCollection();
		$pc = new Timber\PostsCollection();
	}

	function testBasicCollection() {
		$pids = $this->factory->post->create_many(10);
		$pc = new Timber\PostQuery('post_type=post&numberposts=6');
		$this->assertEquals(6, count($pc));
	}

	function testCollectionWithWP_PostArray() {
		$cat = $this->factory->term->create(array('name' => 'Things', 'taxonomy' => 'category'));
		$pids = $this->factory->post->create_many(4, array('category' => $cat));
		$posts = get_posts( array('post_category' => array($cat), 'posts_per_page' => 3) );
		$pc = new Timber\PostQuery($posts);
		$pagination = $pc->pagination();
		$this->assertNull($pagination);
	}

	function testPaginationOnLaterPage() {
		$this->setPermalinkStructure('/%postname%/');
		register_post_type( 'portfolio' );
		$pids = $this->factory->post->create_many( 55, array( 'post_type' => 'portfolio' ) );
		$this->go_to( home_url( '/portfolio/page/3' ) );
		query_posts('post_type=portfolio&paged=3');
		$posts = new Timber\PostQuery();
		$pagination = $posts->pagination();
		$this->assertEquals(6, count($pagination->pages));
	}

	function testBasicCollectionWithPagination() {
		$pids = $this->factory->post->create_many(130);
		$page = $this->factory->post->create(array('post_title' => 'Test', 'post_type' => 'page'));
		$this->go_to('/');
		query_posts(array('post_type=post'));
		$pc = new Timber\PostQuery('post_type=post');
		$str = Timber::compile('assets/collection-pagination.twig', array('posts' => $pc));
		$str = preg_replace('/\s+/', ' ', $str);
		$this->assertEquals('<h1>POST</h1> <h1>POST</h1> <h1>POST</h1> <h1>POST</h1> <h1>POST</h1> <h1>POST</h1> <h1>POST</h1> <h1>POST</h1> <h1>POST</h1> <h1>POST</h1> <div class="l--pagination"> <div class="pagination-inner"> <div class="pagination-previous"> <span class="pagination-previous-link pagination-disabled">Previous</span> </div> <div class="pagination-pages"> <ul class="pagination-pages-list"> <li class="pagination-list-item pagination-page">1</li> <li class="pagination-list-item pagination-seperator">of</li> <li class="pagination-list-item pagination-page">13</li> </ul> </div> <div class="pagination-next"> <a href="http://example.org/?paged=2" class="pagination-next-link ">Next</a> </div> </div> </div>', trim($str));
	}

	function IgnoretestBasicCollectionWithPaginationAndBlankQuery() {
		
		$pids = $this->factory->post->create_many(130);
		$this->go_to('/');
		$pc = new Timber\PostQuery();
		$str = Timber::compile('assets/collection-pagination.twig', array('posts' => $pc));
		$str = preg_replace('/\s+/', ' ', $str);
		$this->assertEquals('<h1>POST</h1> <h1>POST</h1> <h1>POST</h1> <h1>POST</h1> <h1>POST</h1> <h1>POST</h1> <h1>POST</h1> <h1>POST</h1> <h1>POST</h1> <h1>POST</h1> <div class="l--pagination"> <div class="pagination-inner"> <div class="pagination-previous"> <span class="pagination-previous-link pagination-disabled">Previous</span> </div> <div class="pagination-pages"> <ul class="pagination-pages-list"> <li class="pagination-list-item pagination-page">1</li> <li class="pagination-list-item pagination-seperator">of</li> <li class="pagination-list-item pagination-page">13</li> </ul> </div> <div class="pagination-next"> <a href="http://example.org/?paged=2" class="pagination-next-link ">Next</a> </div> </div> </div>', trim($str));
	}

}