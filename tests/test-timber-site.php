<?php

#[AllowDynamicProperties]
class TestTimberSite extends Timber_UnitTestCase
{
    public function testStandardThemeLocation()
    {
        switch_theme('timber-test-theme');

        $site = new Timber\Site();
        $content_subdir = Timber\URLHelper::get_content_subdir();
        $this->assertEquals($content_subdir . '/themes/timber-test-theme', $site->theme->path);

        switch_theme('default');
    }

    public function testLanguageAttributes()
    {
        restore_current_locale();
        $site = new Timber\Site();
        $lang = $site->language_attributes();
        $this->assertEquals('lang="en-US"', $lang);
    }

    public function testChildParentThemeLocation()
    {
        $content_subdir = Timber\URLHelper::get_content_subdir();
        $this->assertFileExists(WP_CONTENT_DIR . '/themes/timber-test-theme-child/style.css');
        switch_theme('timber-test-theme-child');
        $site = new Timber\Site();
        $this->assertEquals($content_subdir . '/themes/timber-test-theme-child', $site->theme->path);
        $this->assertEquals($content_subdir . '/themes/timber-test-theme', $site->theme->parent->path);

        switch_theme('default');
    }

    public function testThemeFromContext()
    {
        switch_theme('timber-test-theme');

        $context = Timber::context();
        $this->assertEquals('timber-test-theme', $context['theme']->slug);

        switch_theme('default');
    }

    public function testThemeFromSiteContext()
    {
        switch_theme('timber-test-theme');

        $context = Timber::context();
        $this->assertEquals('timber-test-theme', $context['site']->theme->slug);

        switch_theme('default');
    }

    public function testSiteURL()
    {
        $site = new Timber\Site();
        $this->assertEquals('http://example.org', $site->link());
        $this->assertEquals(site_url(), $site->site_url);
    }

    public function testHomeUrl()
    {
        $site = new Timber\Site();
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

    public function testSiteCall()
    {
        update_option('foo', 'barr');
        $site = new Timber\Site();

        $twig_string = '{{site.foo}}';
        $result = Timber\Timber::compile_string($twig_string, [
            'site' => $site,
        ]);
        $this->assertEquals('barr', $result);
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
        parent::set_up();
        $this->clean_themes_cache();
    }

    public function tear_down()
    {
        $this->restore_themes();
        parent::tear_down();
    }
}
