<?php

class TestTimberSite extends Timber_UnitTestCase
{
    public function testStandardThemeLocation()
    {
        switch_theme('twentynineteen');
        $site = new \Timber\Site();
        $content_subdir = Timber\URLHelper::get_content_subdir();
        $this->assertEquals($content_subdir . '/themes/twentynineteen', $site->theme->path);
    }

    public function testLanguageAttributes()
    {
        restore_current_locale();
        $site = new \Timber\Site();
        $lang = $site->language_attributes();
        $this->assertEquals('lang="en-US"', $lang);
    }

    public function testChildParentThemeLocation()
    {
        TestTimberLoader::_setupChildTheme();
        $content_subdir = Timber\URLHelper::get_content_subdir();
        $this->assertFileExists(WP_CONTENT_DIR . '/themes/fake-child-theme/style.css');
        switch_theme('fake-child-theme');
        $site = new Timber\Site();
        $this->assertEquals($content_subdir . '/themes/fake-child-theme', $site->theme->path);
        $this->assertEquals($content_subdir . '/themes/twentynineteen', $site->theme->parent->path);
    }

    public function testThemeFromContext()
    {
        switch_theme('twentynineteen');
        $context = Timber::context();
        $this->assertEquals('twentynineteen', $context['theme']->slug);
    }

    public function testThemeFromSiteContext()
    {
        switch_theme('twentynineteen');
        $context = Timber::context();
        $this->assertEquals('twentynineteen', $context['site']->theme->slug);
    }

    public function testSiteURL()
    {
        $site = new \Timber\Site();
        $this->assertEquals('http://example.org', $site->link());
        $this->assertEquals(site_url(), $site->site_url);
    }

    public function testHomeUrl()
    {
        $site = new \Timber\Site();
        $this->assertEquals($site->url, $site->home_url);
    }

    public function testSiteIcon()
    {
        $icon_id = TestTimberImage::get_attachment(0, 'cardinals.jpg');
        update_option('site_icon', $icon_id);
        $site = new Timber\Site();
        $icon = $site->icon();
        $this->assertEquals('Timber\Image', get_class($icon));
        $this->assertStringContainsString('cardinals.jpg', $icon->src());
    }

    public function testNullIcon()
    {
        delete_option('site_icon');
        $site = new Timber\Site();
        $this->assertNull($site->icon());
    }

    public function testSiteGet()
    {
        update_option('foo', 'bar');
        $site = new Timber\Site();
        $this->assertEquals('bar', $site->foo);
    }

    /**
     * @expectedDeprecated {{ site.meta() }}
     */
    public function testSiteMeta()
    {
        $ts = new Timber\Site();
        update_option('foo', 'magoo');
        $this->assertEquals('magoo', $ts->meta('foo'));
    }

    public function testSiteOption()
    {
        $ts = new Timber\Site();
        update_option('date_format', 'j. F Y');
        $this->assertEquals('j. F Y', $ts->option('date_format'));
    }

    public function testWPObject()
    {
        $this->skipWithMultisite();

        $ts = new Timber\Site();
        $this->assertNull($ts->wp_object());
    }

    public function set_up()
    {
        global $wp_theme_directories;

        parent::set_up();

        $this->backup_wp_theme_directories = $wp_theme_directories;
        $wp_theme_directories = [WP_CONTENT_DIR . '/themes'];

        wp_clean_themes_cache();
        unset($GLOBALS['wp_themes']);
    }

    public function tear_down()
    {
        global $wp_theme_directories;

        $wp_theme_directories = $this->backup_wp_theme_directories;

        wp_clean_themes_cache();
        unset($GLOBALS['wp_themes']);
        parent::tear_down();
    }
}
