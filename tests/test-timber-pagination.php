<?php

class TimberPaginationTest extends WP_UnitTestCase {

	function testPaginationSearch(){
		update_option('permalink_structure', '');
		global $wp_rewrite;
		$wp_rewrite->permalink_structure = false;
		$posts = $this->factory->post->create_many(55);
		$this->go_to( home_url( '?s=post' ) );
		$pagination = Timber::get_pagination();
		$this->assertEquals(home_url().'/?s=post&#038;paged=5', $pagination['pages'][4]['link']);
	}

	function testPaginationSearchPrettyWithPostname(){
		$struc = '/%postname%/';
		global $wp_rewrite;
		$wp_rewrite->permalink_structure = $struc;
		update_option('permalink_structure', $struc);
		$posts = $this->factory->post->create_many(55);
		$archive = home_url( '?s=post' );
		$this->go_to( $archive );
		$pagination = Timber::get_pagination();
		$this->assertEquals('http://example.org/page/5/?s=post', $pagination['pages'][4]['link']);
	}

	function testPaginationSearchPretty(){
		$struc = '/blog/%year%/%monthnum%/%postname%/';
		global $wp_rewrite;
		$wp_rewrite->permalink_structure = $struc;
		update_option('permalink_structure', $struc);
		$posts = $this->factory->post->create_many(55);
		$archive = home_url( '?s=post' );
		$this->go_to( $archive );
		$pagination = Timber::get_pagination();
		$this->assertEquals('http://example.org/page/5/?s=post', $pagination['pages'][4]['link']);
	}

	function testPaginationHomePretty($struc = '/%postname%/'){
		global $wp_rewrite;
		$wp_rewrite->permalink_structure = $struc;
		update_option('permalink_structure', $struc);
		$posts = $this->factory->post->create_many(55);
		$this->go_to( home_url( '/' ) );
		$pagination = Timber::get_pagination();
		$this->assertEquals('http://example.org/page/3', $pagination['pages'][2]['link']);
		$this->assertEquals('http://example.org/page/2', $pagination['next']['link']);
	}



}
