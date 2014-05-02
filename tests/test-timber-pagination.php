<?php

class TimberPaginationTest extends WP_UnitTestCase {

	function testPaginationSearch(){
		update_option('permalink_structure', '');
		global $wp_rewrite;
		$wp_rewrite->permalink_structure = false;
		$posts = $this->factory->post->create_many(55);
		$this->go_to( site_url( '?s=post' ) );
		$pagination = Timber::get_pagination();
		$this->assertEquals('http://example.org/?s=post&#038;paged=5', $pagination['pages'][4]['link']);
	}

	function testPaginationSearchPretty(){
		self::run_testPaginationSearchPretty('/%postname%/');
		self::run_testPaginationSearchPretty('/blog/%year%/%monthnum%/%postname%/');
	}

	function run_testPaginationSearchPretty($struc = false){
		global $wp_rewrite;
		$wp_rewrite->permalink_structure = $struc;
		update_option('permalink_structure', $struc);
		$posts = $this->factory->post->create_many(55);
		$this->go_to( site_url( '?s=post' ) );
		$pagination = Timber::get_pagination();
		$this->assertEquals('http://example.org/page/5/?s=post', $pagination['pages'][4]['link']);
	}

	function testPaginationHomePretty($struc = '/%postname%/'){
		global $wp_rewrite;
		$wp_rewrite->permalink_structure = $struc;
		update_option('permalink_structure', $struc);
		$posts = $this->factory->post->create_many(55);
		$this->go_to( site_url( '/' ) );
		$pagination = Timber::get_pagination();
		$this->assertEquals('http://example.org/page/2', $pagination['pages'][1]['link']);
		$this->assertEquals('http://example.org/page/2', $pagination['next']['link']);
	}



}
