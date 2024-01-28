<?php

class TestTimberSite extends Timber_UnitTestCase {
	protected $backup_wp_theme_directories;

	function testStandardThemeLocation() {
		switch_theme( 'twentyfifteen' );
		$site = new TimberSite();
		$content_subdir = Timber\URLHelper::get_content_subdir();
		$this->assertEquals( $content_subdir.'/themes/twentyfifteen', $site->theme->path );
	}

	function testLanguageAttributes() {
		restore_previous_locale();
		$site = new TimberSite();
		$lang = $site->language_attributes();
		$this->assertEquals('lang="en-US"', $lang);
	}

	function testChildParentThemeLocation() {
		TestTimberLoader::_setupChildTheme();
		$content_subdir = Timber\URLHelper::get_content_subdir();
		$this->assertFileExists( WP_CONTENT_DIR.'/themes/fake-child-theme/style.css' );
		switch_theme( 'fake-child-theme' );
		$site = new TimberSite();
		$this->assertEquals( $content_subdir.'/themes/fake-child-theme', $site->theme->path );
		$this->assertEquals( $content_subdir.'/themes/twentyfifteen', $site->theme->parent->path );
	}

	function testThemeFromContext() {
		switch_theme( 'twentyfifteen' );
		$context = Timber::context();
		$this->assertEquals( 'twentyfifteen', $context['theme']->slug );
	}

	function testThemeFromSiteContext() {
		switch_theme( 'twentyfifteen' );
		$context = Timber::context();
		$this->assertEquals( 'twentyfifteen', $context['site']->theme->slug );
	}

	function testSiteURL() {
		$site = new \Timber\Site();
		$this->assertEquals( 'http://example.org', $site->link() );
		$this->assertEquals(site_url(), $site->site_url);
	}

	function testHomeUrl() {
		$site = new \Timber\Site();
		$this->assertEquals($site->url, $site->home_url);
	}

	function testSiteIcon() {
		$icon_id = TestTimberImage::get_image_attachment(0, 'cardinals.jpg');
		update_option('site_icon', $icon_id);
		$site = new TimberSite();
		$icon = $site->icon();
		$this->assertEquals('Timber\Image', get_class($icon));
		$this->assertStringContainsString('cardinals.jpg', $icon->src());
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

	function set_up() {
		global $wp_theme_directories;

		parent::set_up();

		$this->backup_wp_theme_directories = $wp_theme_directories;
		$wp_theme_directories = array( WP_CONTENT_DIR . '/themes' );

		wp_clean_themes_cache();
		unset( $GLOBALS['wp_themes'] );

	}

	function tear_down() {
		global $wp_theme_directories;

		$wp_theme_directories = $this->backup_wp_theme_directories;

		wp_clean_themes_cache();
		unset( $GLOBALS['wp_themes'] );
		parent::tear_down();
	}
}
