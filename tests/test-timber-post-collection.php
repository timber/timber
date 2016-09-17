<?php

class TestTimberPostCollection extends Timber_UnitTestCase {

	function setUp() {
		global $wpdb;
		$wpdb->query("TRUNCATE TABLE $wpdb->posts"); 
		$wpdb->query("ALTER TABLE $wpdb->posts AUTO_INCREMENT = 1");
		parent::setUp();
	}

	function testBasicCollection() {
		$pids = $this->factory->post->create_many(10);
		$pc = new Timber\PostCollection('post_type=post&numberposts=6');
		$this->assertEquals(6, count($pc));
	}

	function testBasicCollectionWithPagination() {
		$pids = $this->factory->post->create_many(130);
		$page = $this->factory->post->create(array('post_title' => 'Test', 'post_type' => 'page'));
		$this->go_to('/test');
		$pc = new Timber\PostCollection('post_type=post');
		$str = Timber::compile('assets/collection-pagination.twig', array('posts' => $pc));
		$str = preg_replace('/\s+/', ' ', $str);
		$this->assertEquals('<h1>POST</h1> <h1>POST</h1> <h1>POST</h1> <h1>POST</h1> <h1>POST</h1> <h1>POST</h1> <h1>POST</h1> <h1>POST</h1> <h1>POST</h1> <h1>POST</h1> <div class="l--pagination"> <div class="pagination-inner"> <div class="pagination-previous"> <span class="pagination-previous-link pagination-disabled">Previous</span> </div> <div class="pagination-pages"> <ul class="pagination-pages-list"> <li class="pagination-list-item pagination-page">1</li> <li class="pagination-list-item pagination-seperator">of</li> <li class="pagination-list-item pagination-page">13</li> </ul> </div> <div class="pagination-next"> <a href="http://example.org/?paged=2" class="pagination-next-link ">Next</a> </div> </div> </div>', trim($str));
	}

	function testBasicCollectionWithPaginationAndBlankQuery() {
		
		$pids = $this->factory->post->create_many(130);
		$this->go_to('/');
		$pc = new Timber\PostCollection();
		$str = Timber::compile('assets/collection-pagination.twig', array('posts' => $pc));
		$str = preg_replace('/\s+/', ' ', $str);
		$this->assertEquals('<h1>POST</h1> <h1>POST</h1> <h1>POST</h1> <h1>POST</h1> <h1>POST</h1> <h1>POST</h1> <h1>POST</h1> <h1>POST</h1> <h1>POST</h1> <h1>POST</h1> <div class="l--pagination"> <div class="pagination-inner"> <div class="pagination-previous"> <span class="pagination-previous-link pagination-disabled">Previous</span> </div> <div class="pagination-pages"> <ul class="pagination-pages-list"> <li class="pagination-list-item pagination-page">1</li> <li class="pagination-list-item pagination-seperator">of</li> <li class="pagination-list-item pagination-page">13</li> </ul> </div> <div class="pagination-next"> <a href="http://example.org/?paged=2" class="pagination-next-link ">Next</a> </div> </div> </div>', trim($str));
	}

}