<?php

class TestTimberTheme extends Timber_UnitTestCase
{
    public $theme_slug = 'twentythirty';

    public function testThemeVersion()
    {
        switch_theme($this->theme_slug);
        $theme = new Timber\Theme();
        $this->assertGreaterThan(1.2, $theme->version);
        switch_theme('default');
    }

    public function testThemeParentWithNoParent()
    {
        switch_theme('twentynineteen');
        $context = Timber::context();
        $theme = $context['site']->theme;
        $output = Timber::compile_string('{{ site.theme.parent.slug }}', $context);
        $this->assertEquals('twentynineteen', $output);
    }

    public function testThemeMods()
    {
        set_theme_mod('foo', 'bar');
        $theme = new Timber\Theme();
        $mods = $theme->theme_mods();
        $this->assertEquals('bar', $mods['foo']);
        $bar = $theme->theme_mod('foo');
        $this->assertEquals('bar', $bar);
    }

    public function testPath()
    {
        $context = Timber::context();
        $theme = $context['site']->theme;
        $output = Timber::compile_string('{{site.theme.path}}', $context);
        $this->assertEquals('/wp-content/themes/' . $theme->slug, $output);
    }

    public function testPathWithPort()
    {
        /* setUp */
        update_option('siteurl', 'http://example.org:3000', true);
        update_option('home', 'http://example.org:3000', true);
        self::setPermalinkStructure();
        $old_port = $_SERVER['SERVER_PORT'];
        $_SERVER['SERVER_PORT'] = 3000;
        if (!isset($_SERVER['SERVER_NAME'])) {
            $_SERVER['SERVER_NAME'] = 'example.org';
        }

        /* test */
        $theme = new Timber\Theme();
        $this->assertEquals('/wp-content/themes/default', $theme->path());

        /* tearDown */
        $_SERVER['SERVER_PORT'] = $old_port;
        update_option('siteurl', 'http://example.org', true);
        update_option('home', 'http://example.org', true);
    }

    public function testPathOnSubdirectoryInstall()
    {
        update_option('siteurl', 'http://example.org/wordpress', true);
        $context = Timber::context();
        $theme = $context['site']->theme;
        $output = Timber::compile_string('{{site.theme.path}}', $context);
        $this->assertEquals('/wp-content/themes/' . $theme->slug, $output);
    }

    public function testLink()
    {
        $context = Timber::context();
        $theme = $context['site']->theme;
        $output = Timber::compile_string('{{site.theme.link}}', $context);
        $this->assertEquals('http://example.org/wp-content/themes/' . $theme->slug, $output);
    }

    public function testLinkOnSubdirectoryInstall()
    {
        update_option('siteurl', 'http://example.org/wordpress', true);
        $context = Timber::context();
        $theme = $context['site']->theme;
        $output = Timber::compile_string('{{site.theme.link}}', $context);
        $this->assertEquals('http://example.org/wp-content/themes/' . $theme->slug, $output);
    }

    public function testThemeGet()
    {
        switch_theme($this->theme_slug);
        $context = Timber::context();
        $output = Timber::compile_string('{{site.theme.get("Name")}}', $context);
        $this->assertEquals('Twenty Nineteen', $output);
        switch_theme('default');
    }

    public function testThemeDisplay()
    {
        switch_theme($this->theme_slug);
        $context = Timber::context();
        $output = Timber::compile_string('{{site.theme.display("Description")}}', $context);
        $this->assertEquals("Our 2019 default theme is designed to show off the power of the block editor. It features custom styles for all the default blocks, and is built so that what you see in the editor looks like what you&#8217;ll see on your website. Twenty Nineteen is designed to be adaptable to a wide range of websites, whether you’re running a photo blog, launching a new business, or supporting a non-profit. Featuring ample whitespace and modern sans-serif headlines paired with classic serif body text, it&#8217;s built to be beautiful on all screen sizes.", $output);
        switch_theme('default');
    }

    public function set_up()
    {
        parent::set_up();

        $this->clean_themes_cache();

        $theme = wp_get_theme($this->theme_slug);
        if (!$theme->exists()) {
            $this->markTestSkipped('The ' . $this->theme_slug . ' theme is not available');
        }
    }

    public function tear_down()
    {
        $this->restore_themes();
        parent::tear_down();
    }
}
