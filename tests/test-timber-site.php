<?php

class TestTimberSite extends Timber_UnitTestCase {

	function testStandardThemeLocation() {
		switch_theme( 'twentythirteen' );
		$site = new TimberSite();
		$this->assertEquals( WP_CONTENT_SUBDIR.'/themes/twentythirteen', $site->theme->path );
	}

	function testChildParentThemeLocation() {
		TestTimberLoader::_setupChildTheme();
		$this->assertFileExists( WP_CONTENT_DIR.'/themes/fake-child-theme/style.css' );
		switch_theme( 'fake-child-theme' );
		$site = new TimberSite();
		$this->assertEquals( WP_CONTENT_SUBDIR.'/themes/fake-child-theme', $site->theme->path );
		$this->assertEquals( WP_CONTENT_SUBDIR.'/themes/twentythirteen', $site->theme->parent->path );
	}

	function testThemeFromContext() {
		switch_theme( 'twentythirteen' );
		$context = Timber::get_context();
		$this->assertEquals( 'twentythirteen', $context['theme']->slug );
	}

	function testThemeFromSiteContext() {
		switch_theme( 'twentythirteen' );
		$context = Timber::get_context();
		$this->assertEquals( 'twentythirteen', $context['site']->theme->slug );
	}

	function testSiteURL() {
		$site = new TimberSite();
		$this->assertEquals( 'http://example.org', $site->link() );
	}

	function testSiteGet() {
		update_option( 'foo', 'bar' );
		$site = new TimberSite();
		$this->assertEquals( 'bar', $site->foo );
	}

	function testSiteMeta() {
		$ts = new TimberSite();
		update_option('foo', 'magoo');
		$this->assertEquals('magoo', $ts->meta('foo'));
	}


}
