<?php

class TimberPaginationTest extends WP_UnitTestCase {

	function testPaginationSearch(){
		update_option('permalink_structure', '');
		$posts = $this->factory->post->create_many(55);
		$this->go_to( site_url( '?s=post' ) );
		$pagination = Timber::get_pagination();
		$this->assertEquals('http://example.org/?s=post&#038;paged=5/', $pagination['pages'][4]['link']);
	}

	function testPaginationSearchPretty(){
		echo get_template_directory();
		echo get_stylesheet_directory();
		self::run_testPaginationSearchPretty('/%postname%/');
		self::run_testPaginationSearchPretty('/blog/%year%/%monthnum%/%postname%/');
	}

	function run_testPaginationSearchPretty($struc){
		global $wp_rewrite;
		update_option('permalink_structure', $struc);
		$wp_rewrite->permalink_structure = $struc;
		$posts = $this->factory->post->create_many(55);
		$this->go_to( site_url( '?s=post' ) );
		$pagination = Timber::get_pagination();
		$this->assertEquals('http://example.org/page/5/?s=post', $pagination['pages'][4]['link']);
	}



}
