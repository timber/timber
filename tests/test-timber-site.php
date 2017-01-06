<?php

class TestTimberSite extends Timber_UnitTestCase {

	function testStandardThemeLocation() {
		switch_theme( 'twentyfifteen' );
		$site = new TimberSite();
		$this->assertEquals( WP_CONTENT_SUBDIR.'/themes/twentyfifteen', $site->theme->path );
	}

	function testChildParentThemeLocation() {
		TestTimberLoader::_setupChildTheme();
		$this->assertFileExists( WP_CONTENT_DIR.'/themes/fake-child-theme/style.css' );
		switch_theme( 'fake-child-theme' );
		$site = new TimberSite();
		$this->assertEquals( WP_CONTENT_SUBDIR.'/themes/fake-child-theme', $site->theme->path );
		$this->assertEquals( WP_CONTENT_SUBDIR.'/themes/twentyfifteen', $site->theme->parent->path );
	}

	function testThemeFromContext() {
		switch_theme( 'twentyfifteen' );
		$context = Timber::get_context();
		$this->assertEquals( 'twentyfifteen', $context['theme']->slug );
	}

	function testThemeFromSiteContext() {
		switch_theme( 'twentyfifteen' );
		$context = Timber::get_context();
		$this->assertEquals( 'twentyfifteen', $context['site']->theme->slug );
	}

	function testSiteURL() {
		$site = new TimberSite();
		$this->assertEquals( 'http://example.org', $site->link() );
	}

	function testSiteIcon() {
		$icon_id = TestTimberImage::get_image_attachment(0, 'cardinals.jpg');
		update_option('site_icon', $icon_id);
		$site = new TimberSite();
		$icon = $site->icon();
		$this->assertEquals('Timber\Image', get_class($icon));
		$this->assertContains('cardinals.jpg', $icon->src());
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

	function setUp() {
		global $wp_theme_directories;

		parent::setUp();

		$this->backup_wp_theme_directories = $wp_theme_directories;
		$wp_theme_directories = array( WP_CONTENT_DIR . '/themes' );

		wp_clean_themes_cache();
		unset( $GLOBALS['wp_themes'] );

	}

	function tearDown() {
		global $wp_theme_directories;

		$wp_theme_directories = $this->backup_wp_theme_directories;

		wp_clean_themes_cache();
		unset( $GLOBALS['wp_themes'] );
		parent::tearDown();
	}
}
